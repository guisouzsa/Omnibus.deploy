<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasIndex('expenses', 'expenses_driver_created_at_idx')) {
                $table->index(['driver_id', 'created_at'], 'expenses_driver_created_at_idx');
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasIndex('drivers', 'drivers_user_id_idx')) {
                $table->index('user_id', 'drivers_user_id_idx');
            }
        });

        Schema::table('spending_limits', function (Blueprint $table) {
            if (!Schema::hasIndex('spending_limits', 'spending_limits_user_created_at_idx')) {
                $table->index(['user_id', 'created_at'], 'spending_limits_user_created_at_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasIndex('expenses', 'expenses_driver_created_at_idx')) {
                $table->dropIndex('expenses_driver_created_at_idx');
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasIndex('drivers', 'drivers_user_id_idx')) {
                $table->dropIndex('drivers_user_id_idx');
            }
        });

        Schema::table('spending_limits', function (Blueprint $table) {
            if (Schema::hasIndex('spending_limits', 'spending_limits_user_created_at_idx')) {
                $table->dropIndex('spending_limits_user_created_at_idx');
            }
        });
    }
};
