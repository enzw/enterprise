<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('lifecycle_status', 30)->default('DRAFT')->after('status')->index();
            $table->string('receipt_status', 30)->default('NOT_RECEIVED')->after('lifecycle_status')->index();
            $table->string('billing_status', 30)->default('NOT_BILLED')->after('receipt_status')->index();
            $table->string('currency_code', 3)->default('USD')->after('location_id');
            $table->foreignId('issued_by')->nullable()->after('created_by')->constrained('users');
            $table->timestamp('issued_at')->nullable()->after('issued_by');
            $table->foreignId('cancelled_by')->nullable()->after('issued_at')->constrained('users');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->index('order_date');
        });

        Schema::table('item_receipts', function (Blueprint $table) {
            $table->string('posting_status', 30)->default('DRAFT')->after('status')->index();
            $table->foreignId('posted_by')->nullable()->after('created_by')->constrained('users');
            $table->timestamp('posted_at')->nullable()->after('posted_by');
            $table->foreignId('reversed_by')->nullable()->after('posted_at')->constrained('users');
            $table->timestamp('reversed_at')->nullable()->after('reversed_by');
            $table->text('reversal_reason')->nullable()->after('reversed_at');
            $table->index('receipt_date');
        });

        Schema::table('item_receipt_items', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->after('purchase_order_item_id')->constrained('items');
        });

        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->string('bill_type', 30)->default('STANDALONE')->after('bill_number')->index();
            $table->string('lifecycle_status', 30)->default('DRAFT')->after('status')->index();
            $table->string('approval_status', 30)->default('PENDING')->after('lifecycle_status')->index();
            $table->string('payment_status', 30)->default('UNPAID')->after('approval_status')->index();
            $table->foreignId('location_id')->nullable()->after('subsidiary_id')->constrained('locations');
            $table->foreignId('ap_account_id')->nullable()->after('location_id')->constrained('accounts');
            $table->string('currency_code', 3)->default('USD')->after('ap_account_id');
            $table->foreignId('rejected_by')->nullable()->after('approved_by')->constrained('users');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->foreignId('paid_by')->nullable()->after('rejection_reason')->constrained('users');
            $table->timestamp('paid_at')->nullable()->after('paid_by');
            $table->foreignId('credit_warning_confirmed_by')->nullable()->after('paid_at')->constrained('users');
            $table->timestamp('credit_warning_confirmed_at')->nullable()->after('credit_warning_confirmed_by');
            $table->index('bill_date');
            $table->index('due_date');
            $table->index(['vendor_id', 'reference_no']);
        });

        Schema::table('vendor_bill_items', function (Blueprint $table) {
            $table->foreignId('expense_account_id')->nullable()->after('item_id')->constrained('accounts');
            $table->foreignId('department_id')->nullable()->after('expense_account_id')->constrained('departments');
        });

        Schema::table('bill_payments', function (Blueprint $table) {
            $table->string('payment_number')->nullable()->after('id');
            $table->foreignId('vendor_id')->nullable()->after('vendor_bill_id')->constrained('vendors');
            $table->foreignId('subsidiary_id')->nullable()->after('vendor_id')->constrained('subsidiaries');
            $table->string('currency_code', 3)->default('USD')->after('subsidiary_id');
            $table->string('status', 30)->default('POSTED')->after('payment_method')->index();
            $table->foreignId('posted_by')->nullable()->after('created_by')->constrained('users');
            $table->timestamp('posted_at')->nullable()->after('posted_by');
            $table->index('payment_date');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('new_values');
            $table->string('ip_address', 45)->nullable()->after('reason');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->json('metadata')->nullable()->after('user_agent');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event');
        });

        $this->migrateLegacyData();

        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->unique(['vendor_id', 'reference_no'], 'vendor_bills_vendor_reference_unique');
        });

        Schema::table('bill_payments', function (Blueprint $table) {
            $table->unique('payment_number');
        });
    }

    private function migrateLegacyData(): void
    {
        DB::table('purchase_orders')->orderBy('id')->each(function (object $po): void {
            $lifecycle = match ($po->status) {
                'cancelled' => 'CANCELLED',
                'draft', 'pending' => 'DRAFT',
                default => 'OPEN',
            };

            $ordered = (int) DB::table('purchase_order_items')
                ->where('purchase_order_id', $po->id)
                ->sum('quantity_ordered');
            $received = (int) DB::table('purchase_order_items')
                ->where('purchase_order_id', $po->id)
                ->sum('quantity_received');
            $billed = (int) DB::table('purchase_order_items')
                ->where('purchase_order_id', $po->id)
                ->sum('quantity_billed');

            DB::table('purchase_orders')->where('id', $po->id)->update([
                'lifecycle_status' => $lifecycle,
                'receipt_status' => $received <= 0
                    ? 'NOT_RECEIVED'
                    : ($ordered > 0 && $received >= $ordered ? 'FULLY_RECEIVED' : 'PARTIALLY_RECEIVED'),
                'billing_status' => $billed <= 0
                    ? 'NOT_BILLED'
                    : ($ordered > 0 && $billed >= $ordered ? 'FULLY_BILLED' : 'PARTIALLY_BILLED'),
                'issued_by' => $lifecycle === 'OPEN' ? $po->created_by : null,
                'issued_at' => $lifecycle === 'OPEN' ? ($po->updated_at ?? $po->created_at) : null,
                'cancelled_by' => $lifecycle === 'CANCELLED' ? $po->created_by : null,
                'cancelled_at' => $lifecycle === 'CANCELLED' ? ($po->updated_at ?? $po->created_at) : null,
            ]);
        });

        DB::table('item_receipts')->orderBy('id')->each(function (object $receipt): void {
            $posted = $receipt->status === 'received';
            DB::table('item_receipts')->where('id', $receipt->id)->update([
                'posting_status' => $posted ? 'POSTED' : 'DRAFT',
                'posted_by' => $posted ? $receipt->created_by : null,
                'posted_at' => $posted ? ($receipt->updated_at ?? $receipt->created_at) : null,
            ]);
        });

        DB::table('item_receipt_items')->orderBy('id')->each(function (object $line): void {
            $itemId = DB::table('purchase_order_items')
                ->where('id', $line->purchase_order_item_id)
                ->value('item_id');

            DB::table('item_receipt_items')->where('id', $line->id)->update([
                'item_id' => $itemId,
            ]);
        });

        $this->normalizeVendorReferences();

        DB::table('vendor_bills')->orderBy('id')->each(function (object $bill): void {
            $isPoBased = $bill->purchase_order_id !== null;
            $isPaid = $bill->status === 'paid';
            $approval = $isPoBased
                ? 'NOT_REQUIRED'
                : match ($bill->status) {
                    'approved', 'partial', 'paid' => 'APPROVED',
                    'cancelled' => 'REJECTED',
                    default => 'PENDING',
                };
            $locationId = $bill->purchase_order_id
                ? DB::table('purchase_orders')->where('id', $bill->purchase_order_id)->value('location_id')
                : null;
            $apAccountId = DB::table('accounts')
                ->where('subsidiary_id', $bill->subsidiary_id)
                ->where('number', '2110')
                ->value('id');

            DB::table('vendor_bills')->where('id', $bill->id)->update([
                'bill_type' => $isPoBased ? 'PO_BASED' : 'STANDALONE',
                'lifecycle_status' => $bill->status === 'cancelled' ? 'VOID' : ($bill->status === 'draft' ? 'DRAFT' : 'POSTED'),
                'approval_status' => $approval,
                'payment_status' => $isPaid ? 'PAID' : 'UNPAID',
                'location_id' => $locationId,
                'ap_account_id' => $apAccountId,
                'paid_by' => $isPaid ? ($bill->approved_by ?? $bill->created_by) : null,
                'paid_at' => $isPaid ? ($bill->updated_at ?? $bill->created_at) : null,
            ]);
        });

        DB::table('bill_payments')->orderBy('id')->each(function (object $payment): void {
            $bill = DB::table('vendor_bills')->where('id', $payment->vendor_bill_id)->first();
            DB::table('bill_payments')->where('id', $payment->id)->update([
                'payment_number' => sprintf(
                    'BP-%s-%06d',
                    date('Ym', strtotime((string) $payment->payment_date)),
                    $payment->id
                ),
                'vendor_id' => $bill?->vendor_id,
                'subsidiary_id' => $bill?->subsidiary_id,
                'currency_code' => $bill?->currency_code ?? 'USD',
                'status' => 'POSTED',
                'posted_by' => $payment->created_by,
                'posted_at' => $payment->updated_at ?? $payment->created_at,
            ]);
        });
    }

    private function normalizeVendorReferences(): void
    {
        DB::table('vendor_bills')
            ->whereNull('reference_no')
            ->orderBy('id')
            ->each(function (object $bill): void {
                DB::table('vendor_bills')->where('id', $bill->id)->update([
                    'reference_no' => "LEGACY-{$bill->id}",
                ]);
            });

        $duplicates = DB::table('vendor_bills')
            ->select('vendor_id', 'reference_no', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('vendor_id', 'reference_no')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('vendor_bills')
                ->where('vendor_id', $duplicate->vendor_id)
                ->where('reference_no', $duplicate->reference_no)
                ->orderBy('id')
                ->pluck('id')
                ->slice(1);

            foreach ($ids as $id) {
                $suffix = "-LEGACY-{$id}";
                DB::table('vendor_bills')->where('id', $id)->update([
                    'reference_no' => Str::limit(
                        (string) $duplicate->reference_no,
                        100 - strlen($suffix),
                        ''
                    ).$suffix,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropUnique(['payment_number']);
            $table->dropIndex(['payment_date']);
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('posted_by');
            $table->dropConstrainedForeignId('subsidiary_id');
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn(['payment_number', 'currency_code', 'status', 'posted_at']);
        });

        Schema::table('vendor_bill_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('expense_account_id');
        });

        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->dropUnique('vendor_bills_vendor_reference_unique');
            $table->dropIndex(['vendor_id', 'reference_no']);
            $table->dropIndex(['bill_date']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['bill_type']);
            $table->dropIndex(['lifecycle_status']);
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['payment_status']);
            $table->dropConstrainedForeignId('credit_warning_confirmed_by');
            $table->dropConstrainedForeignId('paid_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropConstrainedForeignId('ap_account_id');
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn([
                'bill_type',
                'lifecycle_status',
                'approval_status',
                'payment_status',
                'currency_code',
                'rejected_at',
                'rejection_reason',
                'paid_at',
                'credit_warning_confirmed_at',
            ]);
        });

        Schema::table('item_receipt_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('item_id');
        });

        Schema::table('item_receipts', function (Blueprint $table) {
            $table->dropIndex(['receipt_date']);
            $table->dropIndex(['posting_status']);
            $table->dropConstrainedForeignId('reversed_by');
            $table->dropConstrainedForeignId('posted_by');
            $table->dropColumn(['posting_status', 'posted_at', 'reversed_at', 'reversal_reason']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['order_date']);
            $table->dropIndex(['lifecycle_status']);
            $table->dropIndex(['receipt_status']);
            $table->dropIndex(['billing_status']);
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropConstrainedForeignId('issued_by');
            $table->dropColumn([
                'lifecycle_status',
                'receipt_status',
                'billing_status',
                'currency_code',
                'issued_at',
                'cancelled_at',
                'cancellation_reason',
            ]);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['auditable_type', 'auditable_id']);
            $table->dropIndex(['event']);
            $table->dropColumn(['reason', 'ip_address', 'user_agent', 'metadata']);
        });
    }
};
