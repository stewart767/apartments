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
        Schema::table('rooms', function (Blueprint $table) {
            $table->enum('rental_model', ['per_bed', 'per_room', 'per_unit', 'entire_property', 'per_person'])->default('per_room')->after('room_type_id');
        });

        Schema::create('tenant_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->string('asset_name');
            $table->string('category');
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();
            $table->enum('condition', ['new', 'excellent', 'good', 'fair', 'damaged'])->default('good');
            $table->string('photo_path')->nullable();
            $table->date('registered_date');
            $table->text('notes')->nullable();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_assets');

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('rental_model');
        });
    }
};
