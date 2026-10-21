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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoubleBookingProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_double_book_same_bed()
    {
        $region = Region::create(['name' => 'Dar es Salaam']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Kinondoni']);
        $ward = Ward::create(['district_id' => $district->id, 'name' => 'Makumbusho']);
        $propType = PropertyType::create(['name' => 'Hostel', 'slug' => 'hostel']);
        $roomType = RoomType::create(['name' => 'Single', 'slug' => 'single']);

        $owner = User::create([
            'name' => 'Owner Jane',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant Joe 1',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Joe 2',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
        ]);

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

        $bed = Bed::create([
            'room_id' => $room->id,
            'bed_number' => '101A',
            'price' => 150000,
            'billing_period' => 'monthly',
            'status' => 'available',
        ]);

        $bookingService = app(BookingService::class);

        // First tenant books successfully
        $app1 = $bookingService->createApplication($tenant1, [
            'property_id' => $property->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $this->assertNotNull($app1);

        // Second tenant attempts to book same bed space, must throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("This accommodation is no longer available.");

        $bookingService->createApplication($tenant2, [
            'property_id' => $property->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);
    }
}
