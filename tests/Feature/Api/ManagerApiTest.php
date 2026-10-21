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
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ManagerApiTest extends TestCase
{
    use RefreshDatabase;

    protected $manager;
    protected $owner;
    protected $tenant;
    protected $property;
    protected $room;
    protected $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Property Owner',
            'email' => 'owner@test.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->manager = User::create([
            'name' => 'Manager Dave',
            'email' => 'manager@test.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'status' => 'active',
        ]);

        $this->tenant = User::create([
            'name' => 'Tenant Eve',
            'email' => 'eve@test.com',
            'password' => Hash::make('password123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $region = Region::create(['name' => 'Mwanza']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Nyamagana']);
        $ward = Ward::create(['district_id' => $district->id, 'name' => 'Mirongo']);
        $propType = PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment']);
        $roomType = RoomType::create(['name' => 'Single Room', 'slug' => 'single-room']);

        $this->property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Lake View Apartments',
            'property_type_id' => $propType->id,
            'description' => 'Great apartments by the lake.',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'address' => 'Lake Road',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'per_room',
            'price' => 400000,
            'billing_period' => 'monthly',
        ]);

        $this->room = Room::create([
            'property_id' => $this->property->id,
            'room_number' => 'Lake-101',
            'room_type_id' => $roomType->id,
            'capacity' => 1,
            'occupancy' => 0,
            'rental_model' => 'per_room',
            'price' => 400000,
            'billing_period' => 'monthly',
            'status' => 'active',
        ]);

        // Assign manager to property
        DB::table('property_user')->insert([
            'property_id' => $this->property->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->contract = Contract::create([
            'contract_number' => 'CON-TEST-101',
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'room_id' => $this->room->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'rent_amount' => 400000,
            'deposit_amount' => 400000,
            'billing_frequency' => 'monthly',
            'tenant_accepted' => true,
            'status' => 'accepted',
        ]);
    }

    public function test_manager_can_execute_checkin_via_api(): void
    {
        $token = $this->manager->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/manager/checkins/{$this->contract->id}/checkin", [
                'check_in_date' => now()->toDateString(),
                'deposit_received' => 400000,
                'room_condition_notes' => 'Room is in pristine condition. Key handed over.',
                'keys_count' => 2,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('check_ins', [
            'contract_id' => $this->contract->id,
            'tenant_id' => $this->tenant->id,
            'deposit_received' => 400000,
        ]);

        $this->assertEquals('active', $this->contract->fresh()->status);
        $this->assertEquals(1, $this->room->fresh()->occupancy);
    }

    public function test_manager_can_execute_checkout_with_damage_deductions_via_api(): void
    {
        // First activate contract
        $this->contract->update(['status' => 'active']);
        $this->room->update(['occupancy' => 1]);

        $token = $this->manager->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/manager/checkins/{$this->contract->id}/checkout", [
                'check_out_date' => now()->toDateString(),
                'room_condition_notes' => 'Wall repainting needed in bedroom.',
                'damage_charges' => 50000,
                'deposit_refunded' => 350000,
                'outstanding_settled' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('check_outs', [
            'contract_id' => $this->contract->id,
            'tenant_id' => $this->tenant->id,
            'damage_charges' => 50000,
            'deposit_refunded' => 350000,
        ]);

        $this->assertEquals('terminated', $this->contract->fresh()->status);
        $this->assertEquals(0, $this->room->fresh()->occupancy);
    }
}
