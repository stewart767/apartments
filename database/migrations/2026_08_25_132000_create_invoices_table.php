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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->index();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->onDelete('set null');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->enum('invoice_type', ['rent', 'deposit', 'utility', 'maintenance', 'other'])->default('rent')->index();
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->index();
            $table->enum('status', ['unpaid', 'paid', 'partially_paid', 'overdue'])->default('unpaid')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Update payments table to link to invoice_id
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('contract_id')->constrained('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
        Schema::dropIfExists('invoices');
    }
};
