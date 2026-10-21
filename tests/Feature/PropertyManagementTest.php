<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected PropertyType $propType;
    protected Region $region;
    protected District $district;
    protected Ward $ward;
    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Seed data
        $this->owner = User::create([
            'name' => 'Owner Jack',
            'email' => 'jack@owner.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->propType = PropertyType::create([
            'name' => 'Hostel',
            'slug' => 'hostel',
        ]);

        $this->region = Region::create(['name' => 'Dar es Salaam']);
        $this->district = District::create(['region_id' => $this->region->id, 'name' => 'Kinondoni']);
        $this->ward = Ward::create(['district_id' => $this->district->id, 'name' => 'Ubungo']);
        
        $this->institution = Institution::create([
            'name' => 'University of Dar es Salaam',
            'address' => 'Ubungo, DSM',
        ]);

        \App\Models\OwnerDetail::create([
            'user_id' => $this->owner->id,
            'full_legal_name' => 'Owner Jack',
            'nida' => 'NIDA123456',
            'nationality' => 'Tanzanian',
            'phone' => '+255711111111',
            'email' => 'jack@owner.com',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'verification_status' => 'verified',
        ]);
    }

    public function test_owner_can_view_properties_list(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.properties'));
        $response->assertStatus(200);
        $response->assertViewIs('owner.properties.index');
    }

    public function test_owner_can_view_create_property_form(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.properties.create'));
        $response->assertRedirect(route('owner.properties.wizard'));
    }

    public function test_owner_can_store_new_property(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.properties.store'), [
            'property_name' => 'Jack Residency',
            'property_type_id' => $this->propType->id,
            'description' => 'Comfortable student rooms close to UDSM campus.',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'street' => 'Mlimani St',
            'address' => 'House 5, Mlimani St',
            'nearby_institution_id' => $this->institution->id,
            'distance_from_institution' => 0.5,
            'rent_structure' => 'per_bed',
            'price' => 150000.00,
            'billing_period' => 'monthly',
        ]);

        $response->assertRedirect(route('owner.properties'));
        $response->assertSessionHas('success');

        // Assert database holds the record
        $this->assertDatabaseHas('properties', [
            'property_name' => 'Jack Residency',
            'owner_id' => $this->owner->id,
            'verification_status' => 'pending',
            'status' => 'pending_review',
        ]);
    }

    public function test_owner_can_store_new_property_with_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $image1 = \Illuminate\Http\UploadedFile::fake()->image('room1.jpg');
        $image2 = \Illuminate\Http\UploadedFile::fake()->image('room2.png');

        $response = $this->actingAs($this->owner)->post(route('owner.properties.store'), [
            'property_name' => 'Jack Residency with Images',
            'property_type_id' => $this->propType->id,
            'description' => 'Comfortable student rooms close to UDSM campus.',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'street' => 'Mlimani St',
            'address' => 'House 5, Mlimani St',
            'nearby_institution_id' => $this->institution->id,
            'distance_from_institution' => 0.5,
            'rent_structure' => 'per_bed',
            'price' => 150000.00,
            'billing_period' => 'monthly',
            'images' => [$image1, $image2],
        ]);

        $response->assertRedirect(route('owner.properties'));
        $response->assertSessionHas('success');

        $property = Property::where('property_name', 'Jack Residency with Images')->first();
        $this->assertNotNull($property);

        // Assert images database table contains records
        $this->assertDatabaseHas('property_images', [
            'property_id' => $property->id,
            'is_primary' => true,
        ]);
        $this->assertDatabaseHas('property_images', [
            'property_id' => $property->id,
            'is_primary' => false,
        ]);
    }

    public function test_owner_can_view_edit_property_form(): void
    {
        $property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Existing Property',
            'property_type_id' => $this->propType->id,
            'description' => 'Test description',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Test address',
            'rent_structure' => 'per_room',
            'price' => 120000.00,
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.properties.edit', $property->id));
        $response->assertStatus(200);
        $response->assertViewIs('owner.properties.edit');
        $response->assertViewHas('property');
    }

    public function test_owner_cannot_view_others_property_edit_form(): void
    {
        $otherOwner = User::create([
            'name' => 'Owner Mark',
            'email' => 'mark@owner.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $property = Property::create([
            'owner_id' => $otherOwner->id,
            'property_name' => 'Marks Property',
            'property_type_id' => $this->propType->id,
            'description' => 'Test description',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Test address',
            'rent_structure' => 'per_room',
            'price' => 120000.00,
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.properties.edit', $property->id));
        $response->assertStatus(404); // fails with findOrFail because owner_id constraints
    }

    public function test_owner_can_update_property(): void
    {
        $property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Old Property Name',
            'property_type_id' => $this->propType->id,
            'description' => 'Old description',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Old address',
            'rent_structure' => 'per_room',
            'price' => 120000.00,
        ]);

        $response = $this->actingAs($this->owner)->put(route('owner.properties.update', $property->id), [
            'property_name' => 'Updated Property Name',
            'property_type_id' => $this->propType->id,
            'description' => 'New description',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'street' => 'New St',
            'address' => 'New address',
            'nearby_institution_id' => $this->institution->id,
            'distance_from_institution' => 0.6,
            'rent_structure' => 'per_bed',
            'price' => 160000.00,
            'billing_period' => 'semester',
        ]);

        $response->assertRedirect(route('owner.properties'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'property_name' => 'Updated Property Name',
            'price' => 160000.00,
            'rent_structure' => 'per_bed',
        ]);
    }

    public function test_owner_can_delete_property_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Property with Image',
            'property_type_id' => $this->propType->id,
            'description' => 'Test description',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Test address',
            'rent_structure' => 'per_room',
            'price' => 120000.00,
        ]);

        $image = \App\Models\PropertyImage::create([
            'property_id' => $property->id,
            'file_path' => 'properties/fake-image.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->owner)->delete(route('owner.properties.images.destroy', $image->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('property_images', [
            'id' => $image->id,
        ]);
    }
}
