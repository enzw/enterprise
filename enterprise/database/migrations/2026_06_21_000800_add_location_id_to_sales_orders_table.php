<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_orders', 'location_id')) {
                $table->foreignId('location_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('locations')
                    ->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales_orders', 'location_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('location_id');
            });
        }
    }
};
