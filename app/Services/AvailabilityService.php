<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Contract;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    /**
     * Lock and check if a specific bed is available.
     * Must be run inside a DB transaction for double-booking protection.
     */
    public function isBedAvailableForBooking(int $bedId): bool
    {
        // Select for update locks the row to prevent other concurrent requests from booking
        $bed = Bed::where('id', $bedId)
            ->lockForUpdate()
            ->first();

        if (!$bed) {
            return false;
        }

        // Must be status available and no active tenant
        return $bed->status === 'available' && is_null($bed->current_tenant_id);
    }

    /**
     * Lock and check if a specific room is available.
     * Must be run inside a DB transaction for double-booking protection.
     */
    public function isRoomAvailableForBooking(int $roomId): bool
    {
        $room = Room::where('id', $roomId)
            ->lockForUpdate()
            ->first();

        if (!$room) {
            return false;
        }

        if ($room->status !== 'active') {
            return false;
        }

        $rentalModel = $room->rental_model ?? ($room->property->rent_structure ?? 'per_room');

        if ($rentalModel === 'per_bed') {
            // Rented by bed: check if there is at least one available bed
            return Bed::where('room_id', $roomId)
                ->where('status', 'available')
                ->exists();
        }

        if ($rentalModel === 'per_person') {
            // Rented per person: check if active contracts count is less than capacity
            $activeContracts = Contract::where('room_id', $roomId)
                ->whereIn('status', ['accepted', 'active'])
                ->count();
            return $activeContracts < $room->capacity;
        }

        // Rented by room/unit: check if current occupancy is less than capacity
        // Also ensure no active contract or pending approved application is occupying the room
        $hasActiveTenancy = Contract::where('room_id', $roomId)
            ->whereIn('status', ['accepted', 'active'])
            ->exists();

        if ($hasActiveTenancy) {
            return false;
        }

        return $room->occupancy < $room->capacity;
    }

    /**
     * Lock and check if a specific property (entire unit) is available.
     * Must be run inside a DB transaction for double-booking protection.
     */
    public function isPropertyAvailableForBooking(int $propertyId): bool
    {
        $property = Property::where('id', $propertyId)
            ->lockForUpdate()
            ->first();

        if (!$property) {
            return false;
        }

        if ($property->status !== 'published') {
            return false;
        }

        if ($property->rent_structure !== 'entire_property') {
            return true; // If rented by bed/room, check at room/bed level
        }

        // Check active contracts
        $hasActiveTenancy = Contract::where('property_id', $propertyId)
            ->whereIn('status', ['accepted', 'active'])
            ->exists();

        return !$hasActiveTenancy;
    }

    /**
     * Get summary of availability for a property.
     * Highly optimized, runs aggregated queries.
     */
    public function getAvailabilitySummary(Property $property): array
    {
        $summary = [
            'rent_structure' => $property->rent_structure,
            'total_rooms' => 0,
            'available_rooms' => 0,
            'total_beds' => 0,
            'available_beds' => 0,
            'occupancy_text' => 'Unavailable',
            'is_available' => false,
        ];

        if ($property->rent_structure === 'entire_property') {
            $hasActiveTenancy = Contract::where('property_id', $property->id)
                ->whereIn('status', ['accepted', 'active'])
                ->exists();

            $summary['is_available'] = !$hasActiveTenancy;
            $summary['occupancy_text'] = $hasActiveTenancy ? 'Fully Occupied' : 'Available';
            return $summary;
        }

        if ($property->rent_structure === 'per_room' || $property->rent_structure === 'per_unit') {
            $rooms = Room::where('property_id', $property->id)
                ->where('status', 'active')
                ->get();

            $summary['total_rooms'] = $rooms->count();
            
            // Count rooms without active contract
            $occupiedRoomIds = Contract::where('property_id', $property->id)
                ->whereIn('status', ['accepted', 'active'])
                ->pluck('room_id')
                ->toArray();

            $availableRoomsCount = 0;
            foreach ($rooms as $room) {
                if (!in_index($room->id, $occupiedRoomIds) && $room->occupancy < $room->capacity) {
                    $availableRoomsCount++;
                }
            }

            $summary['available_rooms'] = $availableRoomsCount;
            $summary['is_available'] = $availableRoomsCount > 0;
            $summary['occupancy_text'] = $availableRoomsCount > 0 
                ? "$availableRoomsCount Room(s) Available" 
                : "Fully Occupied";

            return $summary;
        }

        if ($property->rent_structure === 'per_person') {
            $rooms = Room::where('property_id', $property->id)
                ->where('status', 'active')
                ->get();

            $summary['total_rooms'] = $rooms->count();
            
            $availableRoomsCount = 0;
            $totalCapacity = 0;
            $activeContractsCount = 0;

            foreach ($rooms as $room) {
                $activeContracts = Contract::where('room_id', $room->id)
                    ->whereIn('status', ['accepted', 'active'])
                    ->count();
                
                $totalCapacity += $room->capacity;
                $activeContractsCount += $activeContracts;

                if ($activeContracts < $room->capacity) {
                    $availableRoomsCount++;
                }
            }

            $availableSpots = max(0, $totalCapacity - $activeContractsCount);

            $summary['available_rooms'] = $availableRoomsCount;
            $summary['is_available'] = $availableSpots > 0;
            $summary['occupancy_text'] = $availableSpots > 0 
                ? "$availableSpots Spot(s) Available" 
                : "Fully Occupied";

            return $summary;
        }

        if ($property->rent_structure === 'per_bed') {
            // Fetch total and available beds
            $bedsData = DB::table('beds')
                ->join('rooms', 'beds.room_id', '=', 'rooms.id')
                ->where('rooms.property_id', $property->id)
                ->where('rooms.status', 'active')
                ->whereNull('beds.deleted_at')
                ->selectRaw('COUNT(beds.id) as total_beds, SUM(CASE WHEN beds.status = "available" THEN 1 ELSE 0 END) as available_beds')
                ->first();

            $summary['total_beds'] = (int) ($bedsData->total_beds ?? 0);
            $summary['available_beds'] = (int) ($bedsData->available_beds ?? 0);
            $summary['is_available'] = $summary['available_beds'] > 0;
            $summary['occupancy_text'] = $summary['available_beds'] > 0 
                ? $summary['available_beds'] . " Bed(s) Available" 
                : "Fully Occupied";

            return $summary;
        }

        if ($property->rent_structure === 'mixed') {
            $rooms = Room::where('property_id', $property->id)
                ->where('status', 'active')
                ->get();

            $summary['total_rooms'] = $rooms->count();
            
            $availableRoomsCount = 0;
            $totalBeds = 0;
            $availableBeds = 0;
            $availableSpots = 0;

            // Cache occupied rooms for room-level models
            $occupiedRoomIds = Contract::where('property_id', $property->id)
                ->whereIn('status', ['accepted', 'active'])
                ->pluck('room_id')
                ->toArray();

            foreach ($rooms as $room) {
                $rentalModel = $room->rental_model ?? 'per_room';

                if ($rentalModel === 'per_bed') {
                    $roomBeds = Bed::where('room_id', $room->id)->whereNull('deleted_at')->get();
                    $totalBeds += $roomBeds->count();
                    $roomAvailableBeds = $roomBeds->where('status', 'available')->count();
                    $availableBeds += $roomAvailableBeds;
                    if ($roomAvailableBeds > 0) {
                        $availableRoomsCount++;
                    }
                } elseif ($rentalModel === 'per_person') {
                    $activeContracts = Contract::where('room_id', $room->id)
                        ->whereIn('status', ['accepted', 'active'])
                        ->count();
                    $roomAvailableSpots = max(0, $room->capacity - $activeContracts);
                    $availableSpots += $roomAvailableSpots;
                    if ($roomAvailableSpots > 0) {
                        $availableRoomsCount++;
                    }
                } else {
                    // per_room, per_unit, entire_property
                    if (!in_index($room->id, $occupiedRoomIds) && $room->occupancy < $room->capacity) {
                        $availableRoomsCount++;
                    }
                }
            }

            $summary['total_beds'] = $totalBeds;
            $summary['available_beds'] = $availableBeds;

            $isAvailable = ($availableRoomsCount > 0) || ($availableBeds > 0) || ($availableSpots > 0);
            $summary['is_available'] = $isAvailable;

            if ($isAvailable) {
                $parts = [];
                // Count available rooms of non-bed non-person model
                $availOtherRooms = 0;
                foreach ($rooms->whereNotIn('rental_model', ['per_bed', 'per_person']) as $r) {
                    if (!in_index($r->id, $occupiedRoomIds) && $r->occupancy < $r->capacity) {
                        $availOtherRooms++;
                    }
                }

                if ($availOtherRooms > 0) {
                    $parts[] = "$availOtherRooms Room(s)";
                }
                if ($availableBeds > 0) {
                    $parts[] = "$availableBeds Bed(s)";
                }
                if ($availableSpots > 0) {
                    $parts[] = "$availableSpots Spot(s)";
                }

                $summary['occupancy_text'] = implode(', ', $parts) . " Available";
            } else {
                $summary['occupancy_text'] = "Fully Occupied";
            }

            return $summary;
        }

        return $summary;
    }
}

// Helper to support simple PHP array check since standard is in_array
if (!function_exists('in_index')) {
    function in_index($needle, $haystack) {
        return in_array($needle, $haystack);
    }
}
