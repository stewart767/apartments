<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Bed;
use App\Services\BookingService;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_bed_updates_availability_correctly()
    {
        // Create lookup structures
        $region = Region::create(['name' => 'Dar es Salaam']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Kinondoni']);
        $ward = Ward::create(['district_id' => $district->id, 'name' => 'Makumbusho']);
        $propType = PropertyType::create(['name' => 'Hostel', 'slug' => 'hostel']);
        $roomType = RoomType::create(['name' => 'Single', 'slug' => 'single']);

        // Create owner & tenant
        $owner = User::create([
            'name' => 'Owner Jane',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $tenant = User::create([
            'name' => 'Tenant Joe',
            'email' => 'tenant@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
        ]);

        // Create property
        $property = Property::create([
            'owner_id' => $owner->id,
            'property_name' => 'Jane Hostel',
            'property_type_id' => $propType->id,
            'description' => 'Fine living space.',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'address' => 'Makumbusho St',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'per_bed',
        ]);

        // Create room
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'occupancy' => 0,
            'price' => 150000,
            'billing_period' => 'monthly',
            'status' => 'active',
        ]);

        // Create bed
        $bed = Bed::create([
            'room_id' => $room->id,
            'bed_number' => '101A',
            'price' => 150000,
            'billing_period' => 'monthly',
            'status' => 'available',
        ]);

        $availabilityService = app(AvailabilityService::class);
        $bookingService = app(BookingService::class);

        // Assert bed is available
        $this->assertTrue($availabilityService->isBedAvailableForBooking($bed->id));

        // Submit Booking application
        $bookingService->createApplication($tenant, [
            'property_id' => $property->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        // Assert bed status is now reserved (unavailable)
        $this->assertFalse($availabilityService->isBedAvailableForBooking($bed->id));
        $this->assertEquals('reserved', $bed->fresh()->status);
    }
}
