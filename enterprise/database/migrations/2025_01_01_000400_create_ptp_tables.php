<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vendors
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->string('name');
            $table->string('vendor_code')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('credit_used', 15, 2)->default(0);
            $table->enum('payment_terms', ['net30', 'net60', 'net90', 'cod'])->default('net30');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('po_number')->unique();
            $table->string('external_po_number')->nullable();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'partial', 'received', 'cancelled'])->default('draft');
            $table->text('memo')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        // Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->foreignId('department_id')->nullable()->constrained();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->integer('quantity_billed')->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_amount', 15, 2);
            $table->timestamps();
        });

        // Item Receipts
        Schema::create('item_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('receipt_number')->unique();
            $table->date('receipt_date');
            $table->text('memo')->nullable();
            $table->enum('status', ['draft', 'received'])->default('draft');
            $table->timestamps();
        });

        // Item Receipt Items
        Schema::create('item_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_receipt_id')->constrained();
            $table->foreignId('purchase_order_item_id')->constrained();
            $table->integer('quantity_received');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Vendor Bills
        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidiary_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('purchase_order_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->string('bill_number')->unique();
            $table->string('reference_no')->nullable();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->text('memo')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Vendor Bill Items
        Schema::create('vendor_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained();
            $table->foreignId('item_id')->nullable()->constrained();
            $table->string('description');
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('line_amount', 15, 2);
            $table->timestamps();
        });

        // Bill Payments
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained();
            $table->foreignId('ap_account_id')->constrained('accounts');
            $table->foreignId('cash_account_id')->constrained('accounts');
            $table->foreignId('created_by')->constrained('users');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['check', 'bank_transfer', 'cash', 'credit_card'])->default('check');
            $table->string('reference_no')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('vendor_bill_items');
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('item_receipt_items');
        Schema::dropIfExists('item_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('vendors');
    }
};
