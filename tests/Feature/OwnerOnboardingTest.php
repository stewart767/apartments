<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OwnerDetail;
use App\Models\WizardDraft;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Region $region;
    protected District $district;
    protected Ward $ward;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $this->owner = User::create([
            'name' => 'John New Owner',
            'email' => 'newowner@platform.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone' => '+255711222333',
        ]);

        $this->region = Region::create(['name' => 'Dar es Salaam']);
        $this->district = District::create(['region_id' => $this->region->id, 'name' => 'Kinondoni']);
        $this->ward = Ward::create(['district_id' => $this->district->id, 'name' => 'Mwenge']);
    }

    public function test_owner_is_redirected_to_onboarding_if_not_completed(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.properties'));
        $response->assertRedirect(route('owner.onboarding'));
    }

    public function test_owner_can_access_onboarding_form(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.onboarding'));
        $response->assertStatus(200);
        $response->assertViewIs('owner.onboarding.wizard');
        $response->assertViewHasAll(['draft', 'regions', 'districts', 'wards']);

        // Check draft was created
        $this->assertDatabaseHas('wizard_drafts', [
            'user_id' => $this->owner->id,
            'wizard_type' => 'owner_onboarding',
            'current_step' => 2,
        ]);
    }

    public function test_owner_can_save_step2_as_draft_without_full_validation(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.onboarding.save-draft'), [
            'current_step' => 2,
            'action' => 'save_draft',
            'full_legal_name' => 'John Doe Legal',
            // Leave other fields blank
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Check draft has the partial data
        $draft = WizardDraft::where('user_id', $this->owner->id)->first();
        $this->assertNotNull($draft);
        $this->assertEquals('John Doe Legal', $draft->data['full_legal_name']);
        $this->assertEquals(2, $draft->current_step);
    }

    public function test_owner_step2_continues_to_step3_with_validation(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.onboarding.save-draft'), [
            'current_step' => 2,
            'action' => 'next',
            'full_legal_name' => 'John Doe Legal',
            'nida' => '19900101-12345-00001-22',
            'nationality' => 'Tanzanian',
            'phone' => '+255711222333',
            'email' => 'legal@example.com',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'street_address' => 'Ali Hassan Mwinyi Rd',
        ]);

        $response->assertRedirect(route('owner.onboarding'));

        // Check draft has progressed to step 3
        $draft = WizardDraft::where('user_id', $this->owner->id)->first();
        $this->assertNotNull($draft);
        $this->assertEquals(3, $draft->current_step);
        $this->assertEquals('19900101-12345-00001-22', $draft->data['nida']);
    }

    public function test_owner_can_submit_onboarding_profile_successfully(): void
    {
        Storage::fake('public');

        // Initialize draft on step 3 with step 2 data
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $this->owner->id, 'wizard_type' => 'owner_onboarding'],
            ['current_step' => 3, 'data' => []]
        );
        $draft->current_step = 3;
        $draft->data = [
            'full_legal_name' => 'John Doe Legal',
            'nida' => '19900101-12345-00001-22',
            'nationality' => 'Tanzanian',
            'phone' => '+255711222333',
            'email' => 'legal@example.com',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'street_address' => 'Ali Hassan Mwinyi Rd',
        ];
        $draft->save();

        $idFile = UploadedFile::fake()->create('id_card.pdf', 1024);
        $supFile = UploadedFile::fake()->create('land_title.pdf', 2048);

        $response = $this->actingAs($this->owner)->post(route('owner.onboarding.submit'), [
            'id_document' => $idFile,
            'supporting_document' => $supFile,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Check draft deleted
        $this->assertNull(WizardDraft::where('user_id', $this->owner->id)->first());

        // Check OwnerDetail created
        $detail = OwnerDetail::where('user_id', $this->owner->id)->first();
        $this->assertNotNull($detail);
        $this->assertEquals('John Doe Legal', $detail->full_legal_name);
        $this->assertEquals('pending', $detail->verification_status);
        $this->assertNotNull($detail->id_document_path);
        $this->assertNotNull($detail->supporting_document_path);

        Storage::disk('public')->assertExists($detail->id_document_path);
        Storage::disk('public')->assertExists($detail->supporting_document_path);
    }
}
