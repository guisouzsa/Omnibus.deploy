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
        DB::statement('ALTER TABLE vehicles DROP CONSTRAINT IF EXISTS vehicles_driver_id_foreign');
        DB::statement('ALTER TABLE vehicles ALTER COLUMN driver_id DROP NOT NULL');
        DB::statement('ALTER TABLE vehicles ADD CONSTRAINT vehicles_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE vehicles DROP CONSTRAINT IF EXISTS vehicles_driver_id_foreign');
        DB::statement('DELETE FROM vehicles WHERE driver_id IS NULL');
        DB::statement('ALTER TABLE vehicles ALTER COLUMN driver_id SET NOT NULL');
        DB::statement('ALTER TABLE vehicles ADD CONSTRAINT vehicles_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE RESTRICT');
    }
};
