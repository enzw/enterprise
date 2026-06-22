<?php

namespace App\Models;

use App\Enums\VendorBillApprovalStatus;
use App\Enums\VendorBillLifecycleStatus;
use App\Enums\VendorBillPaymentStatus;
use App\Enums\VendorBillType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VendorBill extends Model
{
    protected $table = 'vendor_bills';

    protected $fillable = [
        'subsidiary_id', 'vendor_id', 'purchase_order_id', 'created_by', 'approved_by',
        'rejected_by', 'paid_by', 'credit_warning_confirmed_by', 'location_id',
        'ap_account_id', 'bill_number', 'bill_type', 'reference_no', 'bill_date',
        'due_date', 'status', 'lifecycle_status', 'approval_status', 'payment_status',
        'currency_code', 'subtotal', 'tax_amount', 'total', 'amount_paid', 'memo',
        'approved_at', 'rejected_at', 'rejection_reason', 'paid_at',
        'credit_warning_confirmed_at',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'credit_warning_confirmed_at' => 'datetime',
        'bill_type' => VendorBillType::class,
        'lifecycle_status' => VendorBillLifecycleStatus::class,
        'approval_status' => VendorBillApprovalStatus::class,
        'payment_status' => VendorBillPaymentStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function apAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'ap_account_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }

    public function getOutstandingAmountAttribute(): float
    {
        return max(0, round((float) $this->total - (float) $this->amount_paid, 2));
    }
}
