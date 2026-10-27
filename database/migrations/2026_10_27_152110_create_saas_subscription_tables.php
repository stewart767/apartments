<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Organizations
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('company_reg_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Tanzania');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Link organization_id to users and properties
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('role')->constrained('organizations')->onDelete('set null');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('owner_id')->constrained('organizations')->onDelete('set null');
        });

        // 3. SaaS Plans
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 15, 2);
            $table->decimal('price_annual', 15, 2);
            $table->integer('unit_limit')->default(5);
            $table->integer('property_limit')->default(1);
            $table->integer('staff_limit')->default(1);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans');
            $table->string('billing_interval')->default('monthly'); // monthly, annual
            $table->string('status')->default('trialing')->index(); // trialing, active, past_due, cancelled, expired
            $table->decimal('price', 15, 2);
            $table->string('currency', 10)->default('TZS');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable()->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Subscription Invoices (Separate from tenant rental payments)
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->index();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('set null');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('TZS');
            $table->string('billing_interval')->default('monthly');
            $table->string('status')->default('pending')->index(); // pending, paid, failed, void
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable()->index();
            $table->string('receipt_path')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
        });

        // 6. Onboarding Orders
        Schema::create('onboarding_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('service_tier')->default('basic_setup'); // basic_setup, hostel_training, multi_property, custom
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('TZS');
            $table->string('status')->default('quoted')->index(); // quoted, invoiced, paid, in_progress, completed, cancelled
            $table->text('details')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 7. Featured Listing Promotions
        Schema::create('featured_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('tier')->default('standard'); // standard (5k), extended (10k), premium (15k)
            $table->integer('duration_days')->default(7);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('TZS');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('status')->default('pending_payment')->index(); // pending_payment, active, expired, cancelled
            $table->string('payment_reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_promotions');
        Schema::dropIfExists('onboarding_orders');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::dropIfExists('organizations');
    }
};
