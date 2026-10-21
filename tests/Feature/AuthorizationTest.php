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
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_access_other_owners_contracts()
    {
        $region = Region::create(['name' => 'Dar es Salaam']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Kinondoni']);
        $ward = Ward::create(['district_id' => $district->id, 'name' => 'Makumbusho']);
        $propType = PropertyType::create(['name' => 'Hostel', 'slug' => 'hostel']);
        $roomType = RoomType::create(['name' => 'Single', 'slug' => 'single']);

        // Create 2 Owners & 1 Tenant
        $owner1 = User::create([
            'name' => 'Owner Jane 1',
            'email' => 'owner1@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        \App\Models\OwnerDetail::create([
            'user_id' => $owner1->id,
            'full_legal_name' => 'Owner Jane 1',
            'nida' => 'NIDA111111',
            'nationality' => 'Tanzanian',
            'phone' => '+255711111111',
            'email' => 'owner1@example.com',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'verification_status' => 'verified',
        ]);

        $owner2 = User::create([
            'name' => 'Owner Jane 2',
            'email' => 'owner2@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        \App\Models\OwnerDetail::create([
            'user_id' => $owner2->id,
            'full_legal_name' => 'Owner Jane 2',
            'nida' => 'NIDA222222',
            'nationality' => 'Tanzanian',
            'phone' => '+255722222222',
            'email' => 'owner2@example.com',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'verification_status' => 'verified',
        ]);

        $tenant = User::create([
            'name' => 'Tenant Joe',
            'email' => 'tenant@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
        ]);

        // Create property for Owner 1
        $property1 = Property::create([
            'owner_id' => $owner1->id,
            'property_name' => 'Jane Hostel 1',
            'property_type_id' => $propType->id,
            'description' => 'Fine living space.',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'address' => 'Kinondoni St',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'entire_property',
            'price' => 500000,
            'billing_period' => 'monthly',
        ]);

        // Create contract for Owner 1 property
        $contract = Contract::create([
            'contract_number' => 'CON-TEST-1234',
            'tenant_id' => $tenant->id,
            'property_id' => $property1->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
            'rent_amount' => 500000,
            'deposit_amount' => 500000,
            'billing_frequency' => 'monthly',
            'status' => 'active',
        ]);

        // Access as owner1 - should succeed
        $response1 = $this->actingAs($owner1)->get(route('owner.contracts.show', $contract->id));
        $response1->assertStatus(200);

        // Access as owner2 - should be forbidden (403)
        $response2 = $this->actingAs($owner2)->get(route('owner.contracts.show', $contract->id));
        $response2->assertStatus(403);
    }
}
