<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $dbDriver = Schema::connection(null)->getConnection()->getDriverName();

        if ($dbDriver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'owner', 'manager', 'receptionist', 'agent', 'tenant', 'caretaker', 'accountant') NOT NULL DEFAULT 'tenant'");
            DB::statement("ALTER TABLE property_user MODIFY COLUMN role ENUM('manager', 'receptionist', 'agent', 'caretaker', 'accountant') NOT NULL DEFAULT 'manager'");
        } else {
            // SQLite: Recreate property_user with caretaker/accountant support
            Schema::dropIfExists('property_user');
            Schema::create('property_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('role')->default('manager');
                $table->timestamps();
                $table->unique(['property_id', 'user_id', 'role']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dbDriver = Schema::connection(null)->getConnection()->getDriverName();

        if ($dbDriver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'owner', 'manager', 'receptionist', 'agent', 'tenant') NOT NULL DEFAULT 'tenant'");
            DB::statement("ALTER TABLE property_user MODIFY COLUMN role ENUM('manager', 'receptionist', 'agent') NOT NULL DEFAULT 'manager'");
        }
    }
};
