<?php

namespace App\Models;

use App\Enums\PurchaseOrderBillingStatus;
use App\Enums\PurchaseOrderLifecycleStatus;
use App\Enums\PurchaseOrderReceiptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'subsidiary_id', 'vendor_id', 'location_id', 'created_by',
        'po_number', 'external_po_number', 'order_date', 'expected_delivery_date',
        'status', 'lifecycle_status', 'receipt_status', 'billing_status',
        'currency_code', 'issued_by', 'issued_at', 'cancelled_by', 'cancelled_at',
        'cancellation_reason', 'memo', 'subtotal', 'tax_amount', 'total',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'lifecycle_status' => PurchaseOrderLifecycleStatus::class,
        'receipt_status' => PurchaseOrderReceiptStatus::class,
        'billing_status' => PurchaseOrderBillingStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ItemReceipt::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }
}
