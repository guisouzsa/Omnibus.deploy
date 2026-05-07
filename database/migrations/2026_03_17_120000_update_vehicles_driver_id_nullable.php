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
        // Verifica se a foreign key existe antes de remover (MySQL não suporta IF EXISTS)
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'vehicles'
            AND CONSTRAINT_NAME = 'vehicles_driver_id_foreign'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        if (!empty($foreignKeys)) {
            DB::statement('ALTER TABLE vehicles DROP FOREIGN KEY vehicles_driver_id_foreign');
        }

        DB::statement('ALTER TABLE vehicles MODIFY COLUMN driver_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE vehicles ADD CONSTRAINT vehicles_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'vehicles'
            AND CONSTRAINT_NAME = 'vehicles_driver_id_foreign'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        if (!empty($foreignKeys)) {
            DB::statement('ALTER TABLE vehicles DROP FOREIGN KEY vehicles_driver_id_foreign');
        }

        DB::statement('DELETE FROM vehicles WHERE driver_id IS NULL');
        DB::statement('ALTER TABLE vehicles MODIFY COLUMN driver_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE vehicles ADD CONSTRAINT vehicles_driver_id_foreign FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE RESTRICT');
    }
};
