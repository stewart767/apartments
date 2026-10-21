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
        Schema::create('owner_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();
            $table->string('full_legal_name')->nullable();
            $table->string('nida')->nullable();
            $table->string('nationality')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('ward_id')->nullable()->constrained('wards')->onDelete('set null');
            $table->text('street_address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending')->index();
            $table->string('id_document_path')->nullable();
            $table->string('supporting_document_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_details');
    }
};
