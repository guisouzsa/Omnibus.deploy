<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS expenses_driver_created_at_idx ON expenses (driver_id, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS drivers_user_id_idx ON drivers (user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS spending_limits_user_created_at_idx ON spending_limits (user_id, created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS expenses_driver_created_at_idx');
        DB::statement('DROP INDEX IF EXISTS drivers_user_id_idx');
        DB::statement('DROP INDEX IF EXISTS spending_limits_user_created_at_idx');
    }
};
