<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Building;
use App\Models\Floor;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\WizardDraft;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyWizardTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected PropertyType $propType;
    protected Region $region;
    protected District $district;
    protected Ward $ward;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Seed verified owner
        $this->owner = User::create([
            'name' => 'Verified Owner',
            'email' => 'verified@owner.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->region = Region::create(['name' => 'Dar es Salaam']);
        $this->district = District::create(['region_id' => $this->region->id, 'name' => 'Kinondoni']);
        $this->ward = Ward::create(['district_id' => $this->district->id, 'name' => 'Mwenge']);

        \App\Models\OwnerDetail::create([
            'user_id' => $this->owner->id,
            'full_legal_name' => 'Verified Owner Legal',
            'nida' => 'NIDA1234567',
            'nationality' => 'Tanzanian',
            'phone' => '+255711111111',
            'email' => 'verified@owner.com',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'verification_status' => 'verified',
        ]);

        $this->propType = PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment',
        ]);

        RoomType::create([
            'name' => 'Single Room',
            'slug' => 'single-room',
        ]);
    }

    public function test_owner_can_access_property_wizard(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.properties.wizard'));
        $response->assertStatus(200);
        $response->assertViewIs('owner.properties.wizard');

        $this->assertDatabaseHas('wizard_drafts', [
            'user_id' => $this->owner->id,
            'wizard_type' => 'property_registration',
            'current_step' => 1,
        ]);
    }

    public function test_property_wizard_step1_progresses_to_step2(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.properties.wizard.save-draft'), [
            'current_step' => 1,
            'action' => 'next',
            'property_type_id' => $this->propType->id,
        ]);

        $response->assertRedirect(route('owner.properties.wizard'));

        $draft = WizardDraft::where('user_id', $this->owner->id)->where('wizard_type', 'property_registration')->first();
        $this->assertNotNull($draft);
        $this->assertEquals(2, $draft->current_step);
        $this->assertEquals($this->propType->id, $draft->data['property_type_id']);
    }

    public function test_property_wizard_final_submission_creates_hierarchy_successfully(): void
    {
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $this->owner->id, 'wizard_type' => 'property_registration'],
            ['current_step' => 4, 'data' => []]
        );
        $draft->current_step = 4;

        $roomTypeId = \App\Models\RoomType::first()->id;

        $draft->data = [
            'property_type_id' => $this->propType->id,
            'property_name' => 'Wizard Heights Apartments',
            'description' => 'Dynamic property structure built via wizard.',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Plot 55, Mwenge, Dar es Salaam',
            'rent_structure' => 'per_room',
            'price' => 500000,
            'billing_period' => 'monthly',
            'has_multiple_floors' => 'yes',
            'buildings' => [
                [
                    'name' => 'Block Alpha',
                    'floors' => [
                        ['name' => 'Ground Floor', 'rooms_count' => 1],
                        ['name' => 'First Floor', 'rooms_count' => 1]
                    ]
                ],
                [
                    'name' => 'Block Beta',
                    'floors' => [
                        ['name' => 'Ground Floor', 'rooms_count' => 1]
                    ]
                ]
            ],
            'rooms' => [
                [
                    '_key' => 'b_0_f_0_r_0',
                    'room_number' => 'A-101',
                    'room_type_id' => $roomTypeId,
                    'capacity' => 1,
                    'rental_model' => 'per_room',
                    'beds_count' => 0,
                    'price' => 500000,
                    'billing_period' => 'monthly',
                    'bathroom_type' => 'shared',
                    'kitchen_type' => 'none',
                    'furnished_status' => 'unfurnished',
                    'gender_accommodation' => 'any',
                    'status' => 'active',
                    'description' => '',
                    'amenities' => [],
                    'building_index' => 0,
                    'floor_index' => 0,
                ],
                [
                    '_key' => 'b_0_f_1_r_0',
                    'room_number' => 'A-201',
                    'room_type_id' => $roomTypeId,
                    'capacity' => 1,
                    'rental_model' => 'per_room',
                    'beds_count' => 0,
                    'price' => 500000,
                    'billing_period' => 'monthly',
                    'bathroom_type' => 'shared',
                    'kitchen_type' => 'none',
                    'furnished_status' => 'unfurnished',
                    'gender_accommodation' => 'any',
                    'status' => 'active',
                    'description' => '',
                    'amenities' => [],
                    'building_index' => 0,
                    'floor_index' => 1,
                ],
                [
                    '_key' => 'b_1_f_0_r_0',
                    'room_number' => 'B-101',
                    'room_type_id' => $roomTypeId,
                    'capacity' => 1,
                    'rental_model' => 'per_room',
                    'beds_count' => 0,
                    'price' => 500000,
                    'billing_period' => 'monthly',
                    'bathroom_type' => 'shared',
                    'kitchen_type' => 'none',
                    'furnished_status' => 'unfurnished',
                    'gender_accommodation' => 'any',
                    'status' => 'active',
                    'description' => '',
                    'amenities' => [],
                    'building_index' => 1,
                    'floor_index' => 0,
                ]
            ]
        ];
        $draft->save();

        $response = $this->actingAs($this->owner)->post(route('owner.properties.wizard.submit'));
        $response->assertRedirect(route('owner.properties'));
        $response->assertSessionHas('success');

        // Verify draft was deleted
        $this->assertNull(WizardDraft::where('user_id', $this->owner->id)->where('wizard_type', 'property_registration')->first());

        // Verify Property was created
        $property = Property::where('owner_id', $this->owner->id)->where('property_name', 'Wizard Heights Apartments')->first();
        $this->assertNotNull($property);
        $this->assertEquals('draft', $property->status);
        $this->assertEquals('pending', $property->verification_status);

        // Verify Buildings were created
        $buildings = Building::where('property_id', $property->id)->get();
        $this->assertCount(2, $buildings);
        $this->assertEquals('Block Alpha', $buildings[0]->building_name);
        $this->assertEquals('Block Beta', $buildings[1]->building_name);

        // Verify Floors were created
        $floorsAlpha = Floor::where('building_id', $buildings[0]->id)->get();
        $this->assertCount(2, $floorsAlpha);
        $this->assertEquals('Ground Floor', $floorsAlpha[0]->floor_number_name);
        $this->assertEquals('First Floor', $floorsAlpha[1]->floor_number_name);
    }

    public function test_property_wizard_step3_and_step4_progression_for_normal_house(): void
    {
        // 1. Progress Step 2 to Step 3
        $response = $this->actingAs($this->owner)->post(route('owner.properties.wizard.save-draft'), [
            'current_step' => 2,
            'action' => 'next',
            'property_name' => 'Mwenge House',
            'description' => 'A clean normal house.',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Mwenge St',
        ]);
        $response->assertRedirect(route('owner.properties.wizard'));

        $draft = WizardDraft::where('user_id', $this->owner->id)->first();
        $this->assertEquals(3, $draft->current_step);

        // 2. Progress Step 3 to Step 4 for Normal House (sets rooms_count)
        $propTypeHouse = PropertyType::create(['name' => 'Normal House', 'slug' => 'normal-house']);
        $draft->data = array_merge($draft->data, ['property_type_id' => $propTypeHouse->id]);
        $draft->save();

        $response2 = $this->actingAs($this->owner)->post(route('owner.properties.wizard.save-draft'), [
            'current_step' => 3,
            'action' => 'next',
            'rooms_count' => 3,
        ]);
        $response2->assertRedirect(route('owner.properties.wizard'));

        $draft->refresh();
        $this->assertEquals(4, $draft->current_step);
        $this->assertEquals(3, $draft->data['rooms_count']);

        // 3. Progress Step 4 to Step 5 (mixed structure)
        $response3 = $this->actingAs($this->owner)->post(route('owner.properties.wizard.save-draft'), [
            'current_step' => 4,
            'action' => 'next',
            'rent_structure' => 'mixed',
            'price' => 120000,
            'billing_period' => 'monthly',
        ]);
        $response3->assertRedirect(route('owner.properties.wizard'));

        $draft->refresh();
        $this->assertEquals(5, $draft->current_step);
        $this->assertCount(3, $draft->data['rooms']);
        // Verify rooms defaulted to per_room in mixed property
        $this->assertEquals('per_room', $draft->data['rooms'][0]['rental_model']);
    }

    public function test_property_wizard_submission_with_assets_and_mixed_rental(): void
    {
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $this->owner->id, 'wizard_type' => 'property_registration'],
            ['current_step' => 6, 'data' => []]
        );

        $category = \App\Models\AssetCategory::create([
            'name' => 'Furniture',
            'description' => 'Beds, tables',
        ]);

        $roomTypeId = \App\Models\RoomType::first()->id;

        $draft->data = [
            'property_type_id' => $this->propType->id,
            'property_name' => 'Mixed Assets Mansion',
            'description' => 'Complex mixed rental structure.',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Mixed St 12',
            'rent_structure' => 'mixed',
            'price' => 100000,
            'billing_period' => 'monthly',
            'has_multiple_floors' => 'no',
            'rooms_count' => 2,
            'rooms' => [
                [
                    '_key' => 'single_r_0',
                    'room_number' => 'Room A',
                    'room_type_id' => $roomTypeId,
                    'capacity' => 2,
                    'rental_model' => 'per_room',
                    'price' => 300000,
                    'billing_period' => 'monthly',
                    'bathroom_type' => 'private',
                    'kitchen_type' => 'shared',
                    'furnished_status' => 'semi_furnished',
                    'gender_accommodation' => 'any',
                    'status' => 'active',
                ],
                [
                    '_key' => 'single_r_1',
                    'room_number' => 'Room B',
                    'room_type_id' => $roomTypeId,
                    'capacity' => 4,
                    'rental_model' => 'per_bed',
                    'beds_count' => 4,
                    'price' => 100000,
                    'billing_period' => 'monthly',
                    'bathroom_type' => 'shared',
                    'kitchen_type' => 'none',
                    'furnished_status' => 'unfurnished',
                    'gender_accommodation' => 'male',
                    'status' => 'active',
                ]
            ],
            'assets' => [
                [
                    'category_id' => $category->id,
                    'asset_name' => 'Single Bed frame',
                    'quantity' => 4,
                    'condition' => 'good',
                    'room_key' => 'single_r_1',
                ],
                [
                    'category_id' => $category->id,
                    'asset_name' => 'Lobby Sofa',
                    'quantity' => 1,
                    'condition' => 'new',
                    'room_key' => null,
                ]
            ]
        ];
        $draft->save();

        $response = $this->actingAs($this->owner)->post(route('owner.properties.wizard.submit'));
        $response->assertRedirect(route('owner.properties'));

        $property = Property::where('property_name', 'Mixed Assets Mansion')->first();
        $this->assertNotNull($property);
        $this->assertEquals('mixed', $property->rent_structure);

        // Verify rooms created
        $rooms = Room::where('property_id', $property->id)->get();
        $this->assertCount(2, $rooms);
        $this->assertEquals('per_room', $rooms[0]->rental_model);
        $this->assertEquals('per_bed', $rooms[1]->rental_model);

        // Verify beds generated for room B
        $beds = \App\Models\Bed::where('room_id', $rooms[1]->id)->get();
        $this->assertCount(4, $beds);

        // Verify assets created
        $assets = \App\Models\Asset::where('property_id', $property->id)->get();
        $this->assertCount(5, $assets); // 4 beds + 1 sofa

        $sofa = $assets->where('asset_name', 'Lobby Sofa')->first();
        $this->assertNotNull($sofa);
        $this->assertNull($sofa->room_id);

        $bedFrames = $assets->where('asset_name', 'Single Bed frame');
        $this->assertCount(4, $bedFrames);
        $this->assertEquals($rooms[1]->id, $bedFrames->first()->room_id);
    }

    public function test_property_wizard_submission_with_multiple_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $draft = WizardDraft::create([
            'user_id' => $this->owner->id,
            'wizard_type' => 'property_registration',
            'current_step' => 8,
            'data' => [
                'property_type_id' => $this->propType->id,
                'property_name' => 'Image Mansion',
                'description' => 'A property with images',
                'region_id' => $this->region->id,
                'district_id' => $this->district->id,
                'ward_id' => $this->ward->id,
                'address' => '123 Image St',
                'rent_structure' => 'entire_property',
                'price' => 500000,
                'billing_period' => 'monthly',
                'has_multiple_floors' => 'no',
                'rooms' => [],
                'temp_images' => [
                    'properties/temp/temp1.jpg',
                    'properties/temp/temp2.jpg',
                ]
            ]
        ]);

        // Place mock files in temp disk
        \Illuminate\Support\Facades\Storage::disk('public')->put('properties/temp/temp1.jpg', 'fake content 1');
        \Illuminate\Support\Facades\Storage::disk('public')->put('properties/temp/temp2.jpg', 'fake content 2');

        // Prepare directly uploaded files too
        $file1 = \Illuminate\Http\UploadedFile::fake()->image('image1.jpg');
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('image2.png');

        $response = $this->actingAs($this->owner)->post(route('owner.properties.wizard.submit'), [
            'images' => [$file1, $file2]
        ]);

        $response->assertRedirect(route('owner.properties'));

        $property = Property::where('property_name', 'Image Mansion')->first();
        $this->assertNotNull($property);

        // Verify images saved in database
        $this->assertCount(4, $property->images);

        // Verify primary image is set
        $this->assertNotNull($property->primaryImage);
        $this->assertTrue((bool)$property->primaryImage->is_primary);

        // Verify temp files moved
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists('properties/temp1.jpg');
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists('properties/temp2.jpg');
    }
}
