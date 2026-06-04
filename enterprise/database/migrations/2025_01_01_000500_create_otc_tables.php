<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->string('name');
            $table->string('customer_code')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('credit_used', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Sales Orders
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->string('so_number')->unique();
            $table->string('po_reference')->nullable();
            $table->date('order_date');
            $table->date('requested_delivery_date')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'partial', 'fulfilled', 'packed', 'shipped', 'invoiced', 'cancelled'])->default('draft');
            $table->string('currency_code')->default('USD');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('memo')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Sales Order Items
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->integer('quantity_ordered');
            $table->integer('quantity_fulfilled')->default(0);
            $table->integer('quantity_packed')->default(0);
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_invoiced')->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_amount', 15, 2);
            $table->timestamps();
        });

        // Sales Order Fulfillment - Pick
        Schema::create('order_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('pick_number')->unique();
            $table->date('pick_date');
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pick Items
        Schema::create('pick_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_pick_id')->constrained();
            $table->foreignId('sales_order_item_id')->constrained();
            $table->integer('quantity_to_pick');
            $table->integer('quantity_picked')->default(0);
            $table->timestamps();
        });

        // Sales Order Fulfillment - Pack
        Schema::create('order_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('pack_number')->unique();
            $table->date('pack_date');
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pack Items
        Schema::create('pack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_pack_id')->constrained();
            $table->foreignId('sales_order_item_id')->constrained();
            $table->integer('quantity_to_pack');
            $table->integer('quantity_packed')->default(0);
            $table->timestamps();
        });

        // Sales Order Fulfillment - Ship
        Schema::create('order_ships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('ship_number')->unique();
            $table->date('ship_date');
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Ship Items
        Schema::create('ship_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_ship_id')->constrained();
            $table->foreignId('sales_order_item_id')->constrained();
            $table->integer('quantity_to_ship');
            $table->integer('quantity_shipped')->default(0);
            $table->timestamps();
        });

        // Sales Invoices
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('sales_order_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'approved', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('currency_code')->default('USD');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();
        });

        // Sales Invoice Items
        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained();
            $table->foreignId('sales_order_item_id')->nullable()->constrained();
            $table->foreignId('item_id')->nullable()->constrained();
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_amount', 15, 2);
            $table->timestamps();
        });

        // Customer Payments
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('ar_account_id')->constrained('accounts');
            $table->foreignId('cash_account_id')->constrained('accounts');
            $table->foreignId('created_by')->constrained('users');
            $table->string('payment_number')->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['check', 'bank_transfer', 'cash', 'credit_card'])->default('check');
            $table->string('reference_no')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();
        });

        // Customer Payment Allocations (allocate payment to invoices)
        Schema::create('customer_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_payment_id')->constrained();
            $table->foreignId('sales_invoice_id')->constrained();
            $table->decimal('amount_allocated', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_allocations');
        Schema::dropIfExists('customer_payments');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('ship_items');
        Schema::dropIfExists('order_ships');
        Schema::dropIfExists('pack_items');
        Schema::dropIfExists('order_packs');
        Schema::dropIfExists('pick_items');
        Schema::dropIfExists('order_picks');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('customers');
    }
};
