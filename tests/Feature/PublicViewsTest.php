<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;

class PublicViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('RelianceHomes');
    }

    public function test_search_page_renders_successfully(): void
    {
        $response = $this->get('/search');
        $response->assertStatus(200);
        $response->assertSee('Discover Verified Accommodations');
    }

    public function test_how_it_works_page_renders_successfully(): void
    {
        $response = $this->get('/how-it-works');
        $response->assertStatus(200);
        $response->assertSee('How RelianceHomes Works');
    }

    public function test_property_details_page_renders_successfully(): void
    {
        $owner = User::create([
            'name' => 'Owner Test',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active'
        ]);

        $region = Region::create(['name' => 'Dar es Salaam']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Kinondoni']);
        $ward = \App\Models\Ward::create(['district_id' => $district->id, 'name' => 'Ubungo']);
        $type = PropertyType::create(['name' => 'Hostel', 'slug' => 'hostel', 'description' => 'Student Hostel']);

        $property = Property::create([
            'owner_id' => $owner->id,
            'property_name' => 'Modern Student Villa',
            'property_type_id' => $type->id,
            'region_id' => $region->id,
            'district_id' => $district->id,
            'ward_id' => $ward->id,
            'address' => 'Mlimani Road',
            'description' => 'Stunning student residence with high speed wifi.',
            'status' => 'published',
            'verification_status' => 'approved',
            'rent_structure' => 'entire_property',
            'price' => 200000.00,
            'billing_period' => 'monthly'
        ]);

        $response = $this->get('/property/' . $property->id);
        $response->assertStatus(200);
        $response->assertSee('Modern Student Villa');
        $response->assertSee('200,000');
    }
}
