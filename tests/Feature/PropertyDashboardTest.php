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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $otherOwner;
    protected User $staffUser;
    protected Property $property;
    protected Region $region;
    protected District $district;
    protected Ward $ward;
    protected PropertyType $propType;
    protected RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Seed owners
        $this->owner = User::create([
            'name' => 'Owner Jack',
            'email' => 'jack@owner.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->otherOwner = User::create([
            'name' => 'Owner Rose',
            'email' => 'rose@owner.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->staffUser = User::create([
            'name' => 'Staff Sarah',
            'email' => 'sarah@staff.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $this->region = Region::create(['name' => 'Dar es Salaam']);
        $this->district = District::create(['region_id' => $this->region->id, 'name' => 'Kinondoni']);
        $this->ward = Ward::create(['district_id' => $this->district->id, 'name' => 'Mwenge']);

        // Verify profiles
        \App\Models\OwnerDetail::create([
            'user_id' => $this->owner->id,
            'full_legal_name' => 'Owner Jack Legal',
            'nida' => 'NIDA123',
            'nationality' => 'Tanzanian',
            'phone' => '+255711111111',
            'email' => 'jack@owner.com',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'verification_status' => 'verified',
        ]);

        \App\Models\OwnerDetail::create([
            'user_id' => $this->otherOwner->id,
            'full_legal_name' => 'Owner Rose Legal',
            'nida' => 'NIDA456',
            'nationality' => 'Tanzanian',
            'phone' => '+255722222222',
            'email' => 'rose@owner.com',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'verification_status' => 'verified',
        ]);

        $this->propType = PropertyType::create(['name' => 'Hostel', 'slug' => 'hostel']);
        $this->roomType = RoomType::create(['name' => 'Double', 'slug' => 'double']);

        $this->property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Jack Hostel',
            'property_type_id' => $this->propType->id,
            'description' => 'Spacious hostel.',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Mwenge St',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'per_bed',
            'price' => 150000,
            'billing_period' => 'monthly',
        ]);
    }

    public function test_owner_can_access_own_property_dashboard(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.properties.dashboard', $this->property->id));
        $response->assertStatus(200);
        $response->assertViewIs('owner.properties.dashboard.index');
    }

    public function test_owner_cannot_access_other_owners_property_dashboard(): void
    {
        // Logged in as otherOwner, accessing owner's property dashboard -> forbidden (403)
        $response = $this->actingAs($this->otherOwner)->get(route('owner.properties.dashboard', $this->property->id));
        $response->assertStatus(403);
    }

    public function test_owner_can_configure_buildings_and_floors(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.properties.buildings.store', $this->property->id), [
            'building_name' => 'Block Alpha',
        ]);
        $response->assertRedirect();

        $building = Building::where('property_id', $this->property->id)->where('building_name', 'Block Alpha')->first();
        $this->assertNotNull($building);

        $response2 = $this->actingAs($this->owner)->post(route('owner.properties.floors.store', $this->property->id), [
            'building_id' => $building->id,
            'floor_number_name' => 'First Floor',
        ]);
        $response2->assertRedirect();

        $this->assertDatabaseHas('floors', [
            'building_id' => $building->id,
            'floor_number_name' => 'First Floor',
        ]);
    }

    public function test_owner_can_configure_rooms_and_beds(): void
    {
        $building = Building::create([
            'property_id' => $this->property->id,
            'building_name' => 'Block Alpha',
            'status' => 'active',
        ]);

        $floor = Floor::create([
            'building_id' => $building->id,
            'floor_number_name' => 'Ground Floor',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.properties.rooms.store', $this->property->id), [
            'room_number' => 'Rm 101',
            'room_type_id' => $this->roomType->id,
            'capacity' => 2,
            'price' => 150000,
            'billing_period' => 'monthly',
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'gender_accommodation' => 'any',
            'furnished_status' => 'semi_furnished',
            'bathroom_type' => 'shared',
            'kitchen_type' => 'shared',
        ]);
        $response->assertRedirect();

        $room = Room::where('property_id', $this->property->id)->where('room_number', 'Rm 101')->first();
        $this->assertNotNull($room);

        $response2 = $this->actingAs($this->owner)->post(route('owner.properties.beds.store', $this->property->id), [
            'room_id' => $room->id,
            'bed_number' => 'Bed A',
            'price' => 150000,
            'billing_period' => 'monthly',
        ]);
        $response2->assertRedirect();

        $this->assertDatabaseHas('beds', [
            'room_id' => $room->id,
            'bed_number' => 'Bed A',
            'status' => 'available',
        ]);
    }

    public function test_owner_can_manage_staff(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.properties.staff.store', $this->property->id), [
            'email' => $this->staffUser->email,
            'role' => 'caretaker',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('property_user', [
            'property_id' => $this->property->id,
            'user_id' => $this->staffUser->id,
            'role' => 'caretaker',
        ]);

        $response2 = $this->actingAs($this->owner)->post(route('owner.properties.staff.remove', [$this->property->id, $this->staffUser->id]));
        $response2->assertRedirect();

        $this->assertDatabaseMissing('property_user', [
            'property_id' => $this->property->id,
            'user_id' => $this->staffUser->id,
        ]);
    }
}
