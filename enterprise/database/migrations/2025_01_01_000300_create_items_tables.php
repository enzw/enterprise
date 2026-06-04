<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Items
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->string('name');
            $table->string('sku')->unique();
            $table->enum('type', ['inventory', 'non_inventory', 'service'])->default('inventory');
            $table->text('description')->nullable();
            $table->string('units_type')->default('Each');
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->integer('reorder_level')->default(0);
            $table->integer('reorder_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Item Accounting (COGS, Asset, Income accounts per item)
        Schema::create('item_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->foreignId('cogs_account_id')->nullable()->constrained('accounts');
            $table->foreignId('asset_account_id')->nullable()->constrained('accounts');
            $table->foreignId('income_account_id')->nullable()->constrained('accounts');
            $table->foreignId('expense_account_id')->nullable()->constrained('accounts');
            $table->foreignId('tax_schedule_id')->nullable()->constrained('tax_schedules');
            $table->timestamps();
        });

        // Inventory Stock Levels
        Schema::create('inventory_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_on_order')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->timestamps();
            $table->unique(['item_id', 'location_id']);
        });

        // Inventory Adjustments
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('adjustment_account_id')->constrained('accounts');
            $table->string('reference_no')->nullable();
            $table->text('memo')->nullable();
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Inventory Adjustment Items
        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained('inventory_adjustments');
            $table->foreignId('item_id')->constrained();
            $table->integer('quantity_change');
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
        });

        // Inventory Transfers
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');
            $table->foreignId('created_by')->constrained('users');
            $table->string('reference_no')->nullable();
            $table->enum('status', ['draft', 'in_transit', 'received'])->default('draft');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        // Inventory Transfer Items
        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('inventory_transfers');
            $table->foreignId('item_id')->constrained();
            $table->integer('quantity_requested');
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_received')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
        Schema::dropIfExists('inventory_adjustment_items');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('inventory_stock');
        Schema::dropIfExists('item_accounts');
        Schema::dropIfExists('items');
    }
};
