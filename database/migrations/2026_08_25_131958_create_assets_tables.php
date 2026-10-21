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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique()->index();
            $table->string('asset_name');
            $table->foreignId('category_id')->constrained('asset_categories')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->enum('condition', ['new', 'excellent', 'good', 'fair', 'damaged', 'needs_repair', 'missing', 'disposed'])->default('good')->index();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'retired'])->default('available')->index();
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null'); // Owner / manager user who added it
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_handover_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->enum('condition_at_handover', ['new', 'excellent', 'good', 'fair', 'damaged'])->default('good');
            $table->date('handover_date');
            $table->foreignId('staff_id')->constrained('users');
            $table->boolean('tenant_confirmed')->default(false);
            $table->timestamp('tenant_confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_handover_record_id')->constrained('asset_handover_records')->onDelete('cascade');
            $table->enum('original_condition', ['new', 'excellent', 'good', 'fair', 'damaged']);
            $table->enum('current_condition', ['new', 'excellent', 'good', 'fair', 'damaged', 'needs_repair', 'missing', 'disposed']);
            $table->decimal('damage_charges', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('staff_id')->constrained('users');
            $table->date('inspection_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_inspections');
        Schema::dropIfExists('asset_handover_records');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
