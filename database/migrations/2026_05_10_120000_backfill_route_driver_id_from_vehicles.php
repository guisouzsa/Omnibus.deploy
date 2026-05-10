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
        // Backfill route.driver_id for legacy records using vehicle assignments.
        DB::table('routes')
            ->join('vehicles', 'vehicles.route_id', '=', 'routes.id')
            ->whereNull('routes.driver_id')
            ->whereNotNull('vehicles.driver_id')
            ->update(['routes.driver_id' => DB::raw('vehicles.driver_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: avoid data loss on rollback.
    }
};
