<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Subsidiaries/Companies
        Schema::create('subsidiaries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Locations/Warehouses
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->boolean('is_warehouse')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Chart of Accounts
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->string('number')->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tax Schedules
        Schema::create('tax_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 5, 2);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_schedules');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('subsidiaries');
    }
};
