<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sales_invoices DROP CONSTRAINT IF EXISTS sales_invoices_status_check');
            DB::statement("
                ALTER TABLE sales_invoices
                ADD CONSTRAINT sales_invoices_status_check
                CHECK (status IN (
                    'draft',
                    'pending_approval',
                    'approved',
                    'partial',
                    'paid',
                    'overdue',
                    'cancelled'
                ))
            ");

            return;
        }

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'partial',
                'paid',
                'overdue',
                'cancelled',
            ])->default('draft')->change();
        });
    }

    public function down(): void
    {
        DB::table('sales_invoices')
            ->where('status', 'pending_approval')
            ->update(['status' => 'draft']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sales_invoices DROP CONSTRAINT IF EXISTS sales_invoices_status_check');
            DB::statement("
                ALTER TABLE sales_invoices
                ADD CONSTRAINT sales_invoices_status_check
                CHECK (status IN (
                    'draft',
                    'approved',
                    'partial',
                    'paid',
                    'overdue',
                    'cancelled'
                ))
            ");

            return;
        }

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'approved',
                'partial',
                'paid',
                'overdue',
                'cancelled',
            ])->default('draft')->change();
        });
    }
};
