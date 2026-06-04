<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'subsidiary_id', 'name', 'customer_code', 'email', 'phone',
        'address', 'city', 'state', 'country', 'credit_limit',
        'credit_used', 'is_active'
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }
}

class SalesOrder extends Model
{
    protected $fillable = [
        'subsidiary_id', 'customer_id', 'created_by', 'approved_by',
        'so_number', 'po_reference', 'order_date', 'requested_delivery_date',
        'status', 'currency_code', 'subtotal', 'tax_amount', 'total',
        'memo', 'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function picks(): HasMany
    {
        return $this->hasMany(OrderPick::class);
    }

    public function packs(): HasMany
    {
        return $this->hasMany(OrderPack::class);
    }

    public function ships(): HasMany
    {
        return $this->hasMany(OrderShip::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }
}

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id', 'item_id', 'quantity_ordered', 'quantity_fulfilled',
        'quantity_packed', 'quantity_shipped', 'quantity_invoiced',
        'unit_price', 'line_amount'
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
