<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Bed;
use App\Models\Application;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class TenantApiTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $tenant;
    protected $property;
    protected $room;
    protected $bed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Owner Person',
            'email' => 'owner@test.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->tenant = User::create([
            'name' => 'Tenant Person',
            'email' => 'tenant@test.com',
            'password' => Hash::make('password123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $region = Region::create(['name' => 'Dar es Salaam']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Kinondoni']);
        $ward = Ward::create(['district_id' => $district->id, 'name' => 'Mwenge']);
        $propType = PropertyType::create(['name' => 'Student Hostel', 'slug' => 'student-hostel']);
        $roomType = RoomType::create(['name' => 'Single Room', 'slug' => 'single-room']);

        $this->property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Kijitonyama Hostel',
            'property_type_id' => $propType->id,
            'description' => 'Great hostel near university.',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'address' => 'Bagamoyo Rd',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'per_bed',
            'price' => 150000,
            'billing_period' => 'monthly',
        ]);

        $this->room = Room::create([
            'property_id' => $this->property->id,
            'room_number' => 'Room 101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'occupancy' => 0,
            'rental_model' => 'per_bed',
            'price' => 150000,
            'billing_period' => 'monthly',
            'status' => 'active',
        ]);

        $this->bed = Bed::create([
            'room_id' => $this->room->id,
            'bed_number' => '101-A',
            'price' => 150000,
            'billing_period' => 'monthly',
            'status' => 'available',
        ]);
    }

    public function test_tenant_can_search_and_view_properties_via_api(): void
    {
        $response = $this->getJson('/api/v1/properties');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        $detailResponse = $this->getJson("/api/v1/properties/{$this->property->id}");
        $detailResponse->assertStatus(200)
            ->assertJsonPath('data.property_name', 'Kijitonyama Hostel');
    }

    public function test_tenant_can_submit_booking_application_via_api(): void
    {
        $token = $this->tenant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/tenant/applications', [
                'property_id' => $this->property->id,
                'room_id' => $this->room->id,
                'bed_id' => $this->bed->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'tenant_notes' => 'Looking forward to moving in.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.price', 150000);

        $this->assertDatabaseHas('applications', [
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'bed_id' => $this->bed->id,
            'status' => 'pending',
        ]);

        // Bed status should be reserved
        $this->assertEquals('reserved', $this->bed->fresh()->status);
    }

    public function test_cannot_double_book_reserved_bed_via_api(): void
    {
        $secondTenant = User::create([
            'name' => 'Second Tenant',
            'email' => 'second@test.com',
            'password' => Hash::make('pass123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        // Mark bed reserved
        $this->bed->update(['status' => 'reserved']);

        $token = $secondTenant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/tenant/applications', [
                'property_id' => $this->property->id,
                'room_id' => $this->room->id,
                'bed_id' => $this->bed->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This accommodation is no longer available.');
    }
}
