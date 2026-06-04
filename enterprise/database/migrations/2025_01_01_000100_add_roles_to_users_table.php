<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_role')->default('admin')->after('password');
            $table->string('current_role')->default('admin')->after('default_role');
            $table->json('available_roles')->default(json_encode(['admin']))->after('current_role');
            $table->unsignedBigInteger('subsidiary_id')->nullable()->after('available_roles');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['default_role', 'current_role', 'available_roles', 'subsidiary_id']);
        });
    }
};
