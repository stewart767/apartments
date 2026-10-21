<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Application;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class OwnerApiTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $otherOwner;
    protected $tenant;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Owner Alpha',
            'email' => 'alpha@test.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->otherOwner = User::create([
            'name' => 'Owner Beta',
            'email' => 'beta@test.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->tenant = User::create([
            'name' => 'Tenant Charlie',
            'email' => 'charlie@test.com',
            'password' => Hash::make('password123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $region = Region::create(['name' => 'Arusha']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Arusha City']);
        $ward = Ward::create(['district_id' => $district->id, 'name' => 'Sekei']);
        $propType = PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment']);

        $this->property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Alpha Plaza',
            'property_type_id' => $propType->id,
            'description' => 'Luxury apartments in Sekei.',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'address' => 'Njiro Road',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'per_room',
            'price' => 500000,
            'billing_period' => 'monthly',
        ]);
    }

    public function test_owner_can_create_property_via_api(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/owner/properties', [
                'property_name' => 'Beta Suites',
                'property_type_id' => $this->property->property_type_id,
                'description' => 'Brand new units.',
                'region_id' => $this->property->region_id,
                'district_id' => $this->property->district_id,
                'ward_id' => $this->property->ward_id,
                'address' => 'Haile Selassie Rd',
                'rent_structure' => 'entire_property',
                'price' => 1200000,
                'billing_period' => 'monthly',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.property_name', 'Beta Suites');

        $this->assertDatabaseHas('properties', [
            'owner_id' => $this->owner->id,
            'property_name' => 'Beta Suites',
        ]);
    }

    public function test_owner_cannot_access_or_modify_other_owner_property(): void
    {
        $otherToken = $this->otherOwner->createToken('test')->plainTextToken;

        // Attempting to view Alpha's property via Beta's token
        $response = $this->withHeader('Authorization', 'Bearer ' . $otherToken)
            ->getJson("/api/v1/owner/properties/{$this->property->id}");

        $response->assertStatus(404);
    }

    public function test_owner_can_approve_application_and_auto_generate_contract(): void
    {
        $application = Application::create([
            'application_number' => 'APP-TEST-001',
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(12)->toDateString(),
            'price' => 500000,
            'billing_period' => 'monthly',
            'status' => 'pending',
        ]);

        $token = $this->owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/owner/applications/{$application->id}/process", [
                'status' => 'approved',
                'owner_notes' => 'Welcome to Alpha Plaza!',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('approved', $application->fresh()->status);

        // Tenancy contract should be auto-created!
        $this->assertDatabaseHas('contracts', [
            'application_id' => $application->id,
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'status' => 'pending_acceptance',
        ]);
    }
}
