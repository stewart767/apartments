<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Property;
use App\Models\Room;
use App\Models\Bed;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Notifications\HostelSystemNotification;
use Exception;

class BookingService
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Submit a booking reservation.
     * Implements transaction row locking to prevent double booking.
     */
    public function createApplication(User $tenant, array $data): Application
    {
        return DB::transaction(function () use ($tenant, $data) {
            $propertyId = $data['property_id'];
            $roomId = $data['room_id'] ?? null;
            $bedId = $data['bed_id'] ?? null;

            // Fetch property first to understand rent structure
            $property = Property::findOrFail($propertyId);

            // 1. Double Booking Protection Check
            if ($property->rent_structure === 'entire_property') {
                if (!$this->availabilityService->isPropertyAvailableForBooking($propertyId)) {
                    throw new Exception("This accommodation is no longer available.");
                }
            } elseif ($bedId) {
                // Renting specific bed space
                if (!$this->availabilityService->isBedAvailableForBooking($bedId)) {
                    throw new Exception("This accommodation is no longer available.");
                }
            } elseif ($roomId) {
                // Renting specific room unit
                if (!$this->availabilityService->isRoomAvailableForBooking($roomId)) {
                    throw new Exception("This accommodation is no longer available.");
                }
            } else {
                throw new Exception("This accommodation is no longer available.");
            }

            // 2. Determine Price and Billing details based on selected entity
            $price = 0;
            $billingPeriod = 'monthly';

            if ($property->rent_structure === 'entire_property') {
                $price = $property->price;
                $billingPeriod = $property->billing_period;
            } elseif ($bedId) {
                $bed = Bed::find($bedId);
                $price = $bed->price;
                $billingPeriod = $bed->billing_period;
            } elseif ($roomId) {
                $room = Room::find($roomId);
                $price = $room->price;
                $billingPeriod = $room->billing_period;
            }

            // 3. Generate unique application number
            $appNumber = 'APP-' . date('Ymd') . '-' . Str::upper(Str::random(5));

            // 4. Create Booking Application
            $application = Application::create([
                'application_number' => $appNumber,
                'tenant_id' => $tenant->id,
                'property_id' => $propertyId,
                'room_id' => $roomId,
                'bed_id' => $bedId,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'price' => $price,
                'billing_period' => $billingPeriod,
                'status' => 'pending',
                'tenant_notes' => $data['tenant_notes'] ?? null,
            ]);

            // 5. Temporarily lock the room/bed status by marking as 'reserved'
            if ($bedId) {
                Bed::where('id', $bedId)->update(['status' => 'reserved']);
            } elseif ($roomId) {
                Room::where('id', $roomId)->update(['occupancy' => DB::raw('occupancy + 1')]);
            }

            // 6. Log Activity
            ActivityLog::create([
                'user_id' => $tenant->id,
                'action' => 'application_submitted',
                'model_type' => Application::class,
                'model_id' => $application->id,
                'description' => "Booking application {$appNumber} submitted for property {$property->property_name}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify Property Owner
            $owner = $property->owner;
            if ($owner) {
                $owner->notify(new HostelSystemNotification(
                    'New Booking Application',
                    "A new booking application {$appNumber} was submitted for your property {$property->property_name} by {$tenant->name}.",
                    route('owner.applications')
                ));
            }

            // Clear stats caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$property->owner_id}");
            $managerIds = DB::table('property_user')
                ->where('property_id', $property->id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }
            Cache::forget("property_details_{$property->id}");
            Cache::forget('featured_properties');

            return $application;
        });
    }

    /**
     * Process Application Approval or Rejection.
     */
    public function processApplication(Application $application, string $status, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($application, $status, $notes) {
            $oldStatus = $application->status;
            
            if ($oldStatus === $status) {
                return true;
            }

            // Update application status
            $application->update([
                'status' => $status,
                'owner_notes' => $notes
            ]);

            // If approved, auto-generate tenancy contract
            if ($status === 'approved') {
                app(\App\Services\ContractService::class)->generateContract($application);
            }

            $bedId = $application->bed_id;
            $roomId = $application->room_id;

            // Handle resource locks based on status transition
            if ($status === 'rejected' || $status === 'cancelled' || $status === 'expired') {
                // If rejected, free up the bed or reduce room occupancy counter
                if ($bedId) {
                    Bed::where('id', $bedId)->update(['status' => 'available']);
                } elseif ($roomId) {
                    Room::where('id', $roomId)->update(['occupancy' => DB::raw('GREATEST(0, occupancy - 1)')]);
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'application_' . $status,
                'model_type' => Application::class,
                'model_id' => $application->id,
                'description' => "Booking application {$application->application_number} was {$status}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify Tenant
            $tenant = $application->tenant;
            if ($tenant) {
                $tenant->notify(new HostelSystemNotification(
                    "Booking Application " . ucfirst($status),
                    "Your booking application {$application->application_number} for {$application->property->property_name} has been {$status}.",
                    route('tenant.applications')
                ));
            }

            // Clear stats caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$application->property->owner_id}");
            $managerIds = DB::table('property_user')
                ->where('property_id', $application->property_id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }
            Cache::forget("property_details_{$application->property_id}");
            Cache::forget('featured_properties');

            return true;
        });
    }
}
