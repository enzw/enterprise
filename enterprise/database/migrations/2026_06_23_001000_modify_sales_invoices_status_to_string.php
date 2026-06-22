<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing check constraint (PostgreSQL) if it exists
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sales_invoices DROP CONSTRAINT IF EXISTS sales_invoices_status_check');
        }

        // Change column to a plain string (VARCHAR) with default 'draft'
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });
    }

    public function down(): void
    {
        // Revert to enum with original allowed values and add check constraint back
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

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sales_invoices ADD CONSTRAINT sales_invoices_status_check CHECK (status IN ('draft','pending_approval','approved','partial','paid','overdue','cancelled'))");
        }
    }
};
