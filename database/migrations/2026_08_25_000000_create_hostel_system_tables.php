<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Institutions
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        // 2. Location Hierarchy
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->string('name')->index();
            $table->timestamps();
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('name')->index();
            $table->timestamps();
        });

        // 3. Property Types
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Properties
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('property_name');
            $table->foreignId('property_type_id')->constrained('property_types');
            $table->text('description');
            $table->foreignId('region_id')->constrained('regions');
            $table->foreignId('district_id')->constrained('districts');
            $table->foreignId('ward_id')->constrained('wards');
            $table->string('street')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('nearby_institution_id')->nullable()->constrained('institutions')->onDelete('set null');
            $table->decimal('distance_from_institution', 5, 2)->nullable();
            $table->enum('status', ['draft', 'pending_review', 'published', 'suspended', 'archived'])->default('draft')->index();
            $table->enum('verification_status', ['pending', 'approved', 'rejected', 'changes_requested'])->default('pending')->index();
            $table->text('verification_notes')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->enum('rent_structure', ['per_bed', 'per_room', 'per_unit', 'entire_property']);
            $table->decimal('price', 15, 2)->nullable();
            $table->enum('billing_period', ['daily', 'weekly', 'monthly', 'semester', 'six_months', 'annual'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Property Managers / Agents Assignment (Pivot)
        Schema::create('property_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['manager', 'receptionist', 'agent'])->default('manager');
            $table->timestamps();
            $table->unique(['property_id', 'user_id', 'role']);
        });

        // 6. Buildings
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->string('building_name');
            $table->string('building_code')->nullable();
            $table->integer('number_of_floors')->default(1);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Floors
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->string('floor_number_name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 8. Room Types
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 9. Rooms / Units
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('cascade');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('cascade');
            $table->string('room_number');
            $table->foreignId('room_type_id')->constrained('room_types');
            $table->integer('capacity')->default(1);
            $table->integer('occupancy')->default(0);
            $table->text('description')->nullable();
            $table->enum('gender_accommodation', ['any', 'male', 'female'])->default('any');
            $table->enum('furnished_status', ['unfurnished', 'semi_furnished', 'fully_furnished'])->default('unfurnished');
            $table->enum('bathroom_type', ['private', 'shared'])->default('shared');
            $table->enum('kitchen_type', ['private', 'shared', 'none'])->default('none');
            $table->decimal('price', 15, 2)->nullable();
            $table->enum('billing_period', ['daily', 'weekly', 'monthly', 'semester', 'six_months', 'annual'])->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. Beds
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('bed_number');
            $table->decimal('price', 15, 2)->nullable();
            $table->enum('billing_period', ['daily', 'weekly', 'monthly', 'semester', 'six_months', 'annual'])->nullable();
            $table->enum('status', ['available', 'reserved', 'occupied', 'maintenance', 'unavailable'])->default('available')->index();
            $table->foreignId('current_tenant_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 11. Amenities
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->enum('category', ['property', 'room', 'general'])->default('general');
            $table->timestamps();
        });

        // Pivot Tables for Amenities
        Schema::create('amenity_property', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained('amenities')->onDelete('cascade');
            $table->primary(['property_id', 'amenity_id']);
        });

        Schema::create('amenity_room', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained('amenities')->onDelete('cascade');
            $table->primary(['room_id', 'amenity_id']);
        });

        // 12. Property Images
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('cascade');
            $table->string('file_path');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 13. Tenant Details (Profile extension)
        Schema::create('tenant_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();
            $table->string('national_id_passport')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->boolean('is_student')->default(false);
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('set null');
            $table->string('student_reg_number')->nullable();
            $table->timestamps();
        });

        // 14. Applications / Bookings
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique()->index();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 15, 2);
            $table->string('billing_period');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'cancelled', 'expired'])->default('pending')->index();
            $table->text('owner_notes')->nullable();
            $table->text('tenant_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 15. Tenancy Contracts
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique()->index();
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('set null');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rent_amount', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->string('billing_frequency');
            $table->text('rules')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->boolean('tenant_accepted')->default(false);
            $table->timestamp('tenant_accepted_at')->nullable();
            $table->boolean('owner_accepted')->default(false);
            $table->timestamp('owner_accepted_at')->nullable();
            $table->enum('status', ['draft', 'pending_acceptance', 'accepted', 'active', 'expired', 'terminated'])->default('draft')->index();
            $table->string('pdf_path')->nullable();
            $table->text('termination_reason')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 16. Payment Records
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique()->index();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->enum('payment_type', ['rent', 'deposit', 'booking_fee', 'utility', 'maintenance', 'other'])->default('rent')->index();
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'paid', 'partially_paid', 'failed', 'refunded', 'overdue'])->default('pending')->index();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 17. Check-in records
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade')->unique();
            $table->date('check_in_date');
            $table->decimal('deposit_received', 15, 2)->default(0);
            $table->text('room_condition_notes')->nullable();
            $table->json('inventory_json')->nullable();
            $table->foreignId('staff_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 18. Check-out records
        Schema::create('check_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade')->unique();
            $table->date('check_out_date');
            $table->text('room_condition_notes')->nullable();
            $table->decimal('damage_charges', 15, 2)->default(0);
            $table->decimal('deposit_refunded', 15, 2)->default(0);
            $table->boolean('outstanding_balance_settled')->default(true);
            $table->foreignId('staff_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 19. Maintenance requests
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique()->index();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'emergency'])->default('medium')->index();
            $table->enum('status', ['submitted', 'assigned', 'in_progress', 'resolved', 'closed'])->default('submitted')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 20. Maintenance request images
        Schema::create('maintenance_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained('maintenance_requests')->onDelete('cascade');
            $table->string('file_path');
            $table->timestamps();
        });

        // 21. Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->integer('cleanliness')->default(5);
            $table->integer('security')->default(5);
            $table->integer('water')->default(5);
            $table->integer('electricity')->default(5);
            $table->integer('management')->default(5);
            $table->integer('location')->default(5);
            $table->integer('value_for_money')->default(5);
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(true)->index();
            $table->timestamps();
        });

        // 22. Favorites
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'property_id']);
        });

        // 23. Activity logs (Audit trail)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action')->index();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('maintenance_images');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('check_outs');
        Schema::dropIfExists('check_ins');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('tenant_details');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('amenity_room');
        Schema::dropIfExists('amenity_property');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('property_user');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('property_types');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('institutions');
    }
};
