<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\FeaturedPromotion;
use App\Models\Payment;
use App\Models\Contract;
use App\Services\SubscriptionEntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;

class SaaSSubscriptionPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $owner;
    protected User $tenant;
    protected Plan $starterPlan;
    protected Plan $growthPlan;
    protected Plan $hostelPlan;
    protected PropertyType $propType;
    protected Region $region;
    protected District $district;
    protected Ward $ward;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        \App\Models\OwnerDetail::create([
            'user_id' => $this->owner->id,
            'full_legal_name' => 'John Landlord',
            'nida' => '19900101-12345-00001-20',
            'phone' => '+255712345678',
            'verification_status' => 'verified',
        ]);

        $this->tenant = User::factory()->create([
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $this->region = Region::create(['name' => 'Dar es Salaam']);
        $this->district = District::create(['region_id' => $this->region->id, 'name' => 'Kinondoni']);
        $this->ward = Ward::create(['district_id' => $this->district->id, 'name' => 'Mwenge']);

        $this->propType = PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment',
        ]);

        // Seed default plans
        $this->starterPlan = Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Up to 5 units',
            'price_monthly' => 25000,
            'price_annual' => 250000,
            'unit_limit' => 5,
            'property_limit' => 1,
            'staff_limit' => 1,
            'features' => ['basic_property_management', 'booking_applications'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->growthPlan = Plan::create([
            'name' => 'Growth',
            'slug' => 'growth',
            'description' => 'Up to 20 units',
            'price_monthly' => 60000,
            'price_annual' => 600000,
            'unit_limit' => 20,
            'property_limit' => 3,
            'staff_limit' => 3,
            'features' => ['basic_property_management', 'booking_applications', 'maintenance_tickets'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->hostelPlan = Plan::create([
            'name' => 'Hostel / Apartment',
            'slug' => 'hostel',
            'description' => 'Up to 60 units/beds',
            'price_monthly' => 120000,
            'price_annual' => 1200000,
            'unit_limit' => 60,
            'property_limit' => 10,
            'staff_limit' => 10,
            'features' => ['*'],
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }

    /**
     * Test public pricing page loads with plans and annual savings.
     */
    public function test_public_pricing_page_renders_with_tanzania_currency()
    {
        $response = $this->get('/pricing');

        $response->assertStatus(200);
        $response->assertSee('Starter');
        $response->assertSee('25,000');
        $response->assertSee('250,000');
        $response->assertSee('Growth');
        $response->assertSee('Hostel / Apartment');
        $response->assertSee('TSh');
    }

    /**
     * Test public API returns active plans.
     */
    public function test_public_api_returns_plans_json()
    {
        $response = $this->getJson('/api/v1/plans');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Test owner organization auto-resolution.
     */
    public function test_owner_resolves_organization_automatically()
    {
        $org = $this->owner->resolveOrganization();

        $this->assertInstanceOf(Organization::class, $org);
        $this->assertEquals($this->owner->id, $org->owner_user_id);
        $this->assertDatabaseHas('organizations', [
            'owner_user_id' => $this->owner->id,
        ]);
    }

    /**
     * Test owner subscription order placement.
     */
    public function test_owner_can_place_subscription_order()
    {
        $org = $this->owner->resolveOrganization();

        $response = $this->actingAs($this->owner)->post(route('owner.subscription.subscribe'), [
            'plan_id' => $this->starterPlan->id,
            'billing_interval' => 'annual',
            'payment_method' => 'mpesa',
            'payment_reference' => 'MPESA99120',
            'notes' => 'Annual subscription',
        ]);

        $response->assertRedirect(route('owner.subscription'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'organization_id' => $org->id,
            'plan_id' => $this->starterPlan->id,
            'billing_interval' => 'annual',
            'price' => 250000.00,
        ]);

        $this->assertDatabaseHas('subscription_invoices', [
            'organization_id' => $org->id,
            'amount' => 250000.00,
            'payment_reference' => 'MPESA99120',
            'status' => 'pending',
        ]);
    }

    /**
     * Test admin can verify subscription payment and activate plan.
     */
    public function test_admin_can_verify_and_activate_subscription()
    {
        $org = $this->owner->resolveOrganization();
        $entitlementService = app(SubscriptionEntitlementService::class);

        $order = $entitlementService->createSubscriptionOrder(
            $org,
            $this->starterPlan,
            'monthly',
            'tigopesa',
            'TIGO88192'
        );

        $invoice = $order['invoice'];
        $this->assertEquals('pending', $invoice->status);

        // Non-admin cannot verify
        $forbiddenResponse = $this->actingAs($this->owner)
            ->post(route('admin.subscriptions.verify', $invoice->id), ['action' => 'approve']);
        $forbiddenResponse->assertStatus(403);

        // Admin approves
        $response = $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.verify', $invoice->id), [
                'action' => 'approve',
                'verification_notes' => 'Received via TigoPesa ledger',
            ]);

        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertEquals($this->admin->id, $invoice->verified_by);

        $subscription = $invoice->subscription->fresh();
        $this->assertEquals('active', $subscription->status);
        $this->assertTrue($subscription->isActive());
    }

    /**
     * Test admin can grant promotional trial access to an organization.
     */
    public function test_admin_can_grant_promotional_trial()
    {
        $org = $this->owner->resolveOrganization();

        $response = $this->actingAs($this->admin)->post(route('admin.subscriptions.promo'), [
            'organization_id' => $org->id,
            'plan_id' => $this->growthPlan->id,
            'days' => 21,
            'reason' => 'Campus hostel partnership promo',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'organization_id' => $org->id,
            'plan_id' => $this->growthPlan->id,
            'status' => 'trialing',
        ]);

        $activeSub = $org->active_subscription;
        $this->assertNotNull($activeSub);
        $this->assertEquals('trialing', $activeSub->status);
        $this->assertTrue($activeSub->isTrialing());
    }

    /**
     * Test subscription limits prevent owner from exceeding property limit.
     */
    public function test_subscription_limits_block_adding_properties_beyond_plan_limit()
    {
        $org = $this->owner->resolveOrganization();
        
        // Subscribe to Starter (max 1 property)
        Subscription::create([
            'organization_id' => $org->id,
            'plan_id' => $this->starterPlan->id,
            'billing_interval' => 'monthly',
            'status' => 'active',
            'price' => 25000,
            'currency' => 'TZS',
            'starts_at' => now(),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
        ]);

        // Create 1st property (allowed)
        Property::create([
            'owner_id' => $this->owner->id,
            'organization_id' => $org->id,
            'property_name' => 'First House',
            'property_type_id' => $this->propType->id,
            'description' => 'Test',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Test Street',
            'rent_structure' => 'entire_property',
            'price' => 500000,
            'status' => 'published',
        ]);

        $this->assertEquals(1, $org->total_properties_count);

        // Attempting to create a second property should be blocked by middleware
        $response = $this->actingAs($this->owner)->get(route('owner.properties.create'));
        $response->assertRedirect(route('owner.subscription'));
        $response->assertSessionHas('error');
    }

    /**
     * Test unit counting rule across different rent structures.
     */
    public function test_consistent_unit_counting_rule()
    {
        $org = $this->owner->resolveOrganization();

        // 1 entire property = 1 unit
        $p1 = Property::create([
            'owner_id' => $this->owner->id,
            'organization_id' => $org->id,
            'property_name' => 'Full Villa',
            'property_type_id' => $this->propType->id,
            'description' => 'Villa',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Villa Rd',
            'rent_structure' => 'entire_property',
            'price' => 1200000,
            'status' => 'published',
        ]);

        $this->assertEquals(1, $p1->calculated_units_count);
        $this->assertEquals(1, $org->total_units_count);

        // 1 property with 3 rooms = 3 units
        $roomType = \App\Models\RoomType::create(['name' => 'Single', 'slug' => 'single-unit']);
        $p2 = Property::create([
            'owner_id' => $this->owner->id,
            'organization_id' => $org->id,
            'property_name' => 'Room House',
            'property_type_id' => $this->propType->id,
            'description' => 'Rooms',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Room Rd',
            'rent_structure' => 'per_room',
            'status' => 'published',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            \App\Models\Room::create([
                'property_id' => $p2->id,
                'room_number' => "R0{$i}",
                'room_type_id' => $roomType->id,
                'price' => 100000,
                'status' => 'active',
            ]);
        }

        $this->assertEquals(3, $p2->calculated_units_count);
        $this->assertEquals(4, $org->total_units_count); // 1 + 3 = 4 units
    }

    /**
     * Test featured listing promotion submission and activation.
     */
    public function test_featured_promotion_workflow_and_expiry()
    {
        $org = $this->owner->resolveOrganization();
        $property = Property::create([
            'owner_id' => $this->owner->id,
            'organization_id' => $org->id,
            'property_name' => 'Hostel A',
            'property_type_id' => $this->propType->id,
            'description' => 'Hostel',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Hostel Rd',
            'rent_structure' => 'per_bed',
            'status' => 'published',
            'is_featured' => false,
        ]);

        // Owner orders promotion
        $response = $this->actingAs($this->owner)->post(route('owner.properties.promote', $property->id), [
            'tier' => 'standard',
            'payment_reference' => 'PROMO7711',
        ]);

        $response->assertSessionHas('success');

        $promo = FeaturedPromotion::where('property_id', $property->id)->first();
        $this->assertNotNull($promo);
        $this->assertEquals('pending_payment', $promo->status);
        $this->assertEquals(5000.00, $promo->amount);
        $this->assertFalse($property->fresh()->is_featured);

        // Admin approves
        $this->actingAs($this->admin)->post(route('admin.promotions.verify', $promo->id), [
            'action' => 'approve',
        ]);

        $promo->refresh();
        $this->assertEquals('active', $promo->status);
        $this->assertTrue($promo->isActive());
        $this->assertTrue($property->fresh()->is_featured);

        // Test automatic expiration command
        $promo->update(['expires_at' => now()->subMinute()]);
        Artisan::call('reliance:process-expirations');

        $promo->refresh();
        $this->assertEquals('expired', $promo->status);
        $this->assertFalse($property->fresh()->is_featured);
    }

    /**
     * Test automated rent reminders command.
     */
    public function test_automated_rent_reminders_command()
    {
        $property = Property::create([
            'owner_id' => $this->owner->id,
            'property_name' => 'Hostel Alpha',
            'property_type_id' => $this->propType->id,
            'description' => 'Test',
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'ward_id' => $this->ward->id,
            'address' => 'Test',
            'rent_structure' => 'per_room',
            'status' => 'published',
        ]);

        $contract = Contract::create([
            'contract_number' => 'CON-TEST-001',
            'tenant_id' => $this->tenant->id,
            'property_id' => $property->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'rent_amount' => 200000,
            'deposit_amount' => 200000,
            'billing_frequency' => 'monthly',
            'status' => 'active',
        ]);

        // Overdue rent
        $overduePayment = Payment::create([
            'payment_reference' => 'PAY-OVERDUE-1',
            'tenant_id' => $this->tenant->id,
            'property_id' => $property->id,
            'contract_id' => $contract->id,
            'payment_type' => 'rent',
            'amount' => 200000,
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'pending',
        ]);

        Artisan::call('reliance:send-rent-reminders');

        $this->assertEquals('overdue', $overduePayment->fresh()->status);
    }
}
