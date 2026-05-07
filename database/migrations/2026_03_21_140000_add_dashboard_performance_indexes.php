<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('expenses', function (Blueprint $table) {
                $table->index(['driver_id', 'created_at'], 'expenses_driver_created_at_idx');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('drivers', function (Blueprint $table) {
                $table->index('user_id', 'drivers_user_id_idx');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('spending_limits', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'spending_limits_user_created_at_idx');
            });
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        try {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropIndex('expenses_driver_created_at_idx');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropIndex('drivers_user_id_idx');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('spending_limits', function (Blueprint $table) {
                $table->dropIndex('spending_limits_user_created_at_idx');
            });
        } catch (\Exception $e) {}
    }
};
