<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoice extends Model
{
    protected $fillable = [
        'subsidiary_id', 'customer_id', 'sales_order_id', 'created_by',
        'invoice_number', 'invoice_date', 'due_date', 'status',
        'currency_code', 'subtotal', 'tax_amount', 'total', 'amount_paid',
        'memo'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }
}

class SalesInvoiceItem extends Model
{
    protected $fillable = [
        'sales_invoice_id', 'sales_order_item_id', 'item_id',
        'description', 'quantity', 'unit_price', 'line_amount'
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function soItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

class CustomerPayment extends Model
{
    protected $fillable = [
        'customer_id', 'ar_account_id', 'cash_account_id', 'created_by',
        'payment_number', 'payment_date', 'amount', 'payment_method',
        'reference_no', 'memo'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function arAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'ar_account_id');
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }
}

class CustomerPaymentAllocation extends Model
{
    protected $fillable = [
        'customer_payment_id', 'sales_invoice_id', 'amount_allocated'
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(CustomerPayment::class, 'customer_payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}
