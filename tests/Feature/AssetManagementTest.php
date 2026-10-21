<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetHandoverRecord;
use App\Models\AssetInspection;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $tenant;
    protected Property $property;
    protected Room $room;
    protected AssetCategory $category;
    protected Region $region;
    protected District $district;
    protected Ward $ward;
    protected PropertyType $propType;
    protected RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Seed details
        $this->owner = User::create([
            'name' => 'Owner Alice',
            'email' => 'alice@owner.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->tenant = User::create([
            'name' => 'Tenant Bob',
            'email' => 'bob@tenant.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
        ]);

        $this->region = Region::create(['name' => 'Dar es Salaam']);
        $this->district = District::create(['region_id' => $this->region->id, 'name' => 'Kinondoni']);
        $this->ward = Ward::create(['district_id' => $this->district->id, 'name' => 'Mwenge']);

        // Verify owner profile
        \App\Models\OwnerDetail::create([
            'user_id' => $this->owner->id,
            'full_legal_name' => 'Owner Alice Legal',
            'nida' => 'NIDA12345',
            'nationality' => 'Tanzanian',
            'phone' => '+255711111111',
            'email' => 'alice@owner.com',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'verification_status' => 'verified',
        ]);

        $this->propType = PropertyType::create(['name' => 'Hostel', 'slug' => 'hostel']);
        $this->roomType = RoomType::create(['name' => 'Single', 'slug' => 'single']);

        $this->property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Alice Hostel',
            'property_type_id' => $this->propType->id,
            'description' => 'Hostel under management.',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Mwenge St',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'per_room',
            'price' => 200000,
            'billing_period' => 'monthly',
        ]);

        $this->room = Room::create([
            'property_id' => $this->property->id,
            'room_number' => 'Rm 101',
            'room_type_id' => $this->roomType->id,
            'capacity' => 1,
            'price' => 200000,
            'billing_period' => 'monthly',
            'status' => 'active',
        ]);

        // Create active contract for tenant
        Contract::create([
            'contract_number' => 'CON-12345',
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'room_id' => $this->room->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(6),
            'rent_amount' => 200000,
            'billing_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $this->category = AssetCategory::create([
            'name' => 'Electronics',
            'description' => 'Appliances and remotes',
        ]);
    }

    public function test_owner_can_register_asset(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.properties.assets.store', $this->property->id), [
            'asset_name' => 'AC Remote Control',
            'category_id' => $this->category->id,
            'serial_number' => 'AC-REM-999',
            'condition' => 'new',
            'room_id' => $this->room->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assets', [
            'property_id' => $this->property->id,
            'asset_name' => 'AC Remote Control',
            'condition' => 'new',
            'status' => 'assigned',
        ]);
    }

    public function test_owner_can_handover_asset_to_tenant_and_tenant_can_confirm(): void
    {
        $asset = Asset::create([
            'property_id' => $this->property->id,
            'category_id' => $this->category->id,
            'room_id' => $this->room->id,
            'asset_name' => 'AC Remote Control',
            'asset_code' => 'AST-HANDOVER-TEST',
            'condition' => 'new',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.properties.assets.handover', $this->property->id), [
            'asset_id' => $asset->id,
            'tenant_id' => $this->tenant->id,
            'notes' => 'Please confirm if working.',
        ]);

        $response->assertRedirect();
        
        $ho = AssetHandoverRecord::where('asset_id', $asset->id)->where('tenant_id', $this->tenant->id)->first();
        $this->assertNotNull($ho);
        $this->assertFalse($ho->tenant_confirmed);

        // Tenant confirms receipt
        $response2 = $this->actingAs($this->tenant)->post(route('tenant.assets.confirm', $ho->id));
        $response2->assertRedirect();

        $ho->refresh();
        $this->assertTrue($ho->tenant_confirmed);
        $this->assertNotNull($ho->tenant_confirmed_at);
    }

    public function test_owner_can_inspect_asset_and_log_checkout_condition(): void
    {
        $asset = Asset::create([
            'property_id' => $this->property->id,
            'category_id' => $this->category->id,
            'room_id' => $this->room->id,
            'asset_name' => 'AC Remote Control',
            'asset_code' => 'AST-INSPECT-TEST',
            'condition' => 'new',
            'status' => 'assigned',
        ]);

        $ho = AssetHandoverRecord::create([
            'property_id' => $this->property->id,
            'room_id' => $this->room->id,
            'asset_id' => $asset->id,
            'tenant_id' => $this->tenant->id,
            'handover_date' => now()->subWeeks(2),
            'condition_at_handover' => 'new',
            'staff_id' => $this->owner->id,
            'tenant_confirmed' => true,
        ]);

        // Log checkout inspection with damaged condition
        $response = $this->actingAs($this->owner)->post(route('owner.properties.assets.inspect', $this->property->id), [
            'asset_id' => $asset->id,
            'condition' => 'damaged',
            'type' => 'checkout',
            'notes' => 'Cracked screen, batteries missing.',
        ]);

        $response->assertRedirect();

        $asset->refresh();
        $this->assertEquals('damaged', $asset->condition);
        $this->assertEquals('maintenance', $asset->status);

        // Verify inspection logged in database
        $this->assertDatabaseHas('asset_inspections', [
            'asset_handover_record_id' => $ho->id,
            'current_condition' => 'damaged',
            'staff_id' => $this->owner->id,
        ]);
    }
}
