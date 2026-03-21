<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->string('start_point_cep', 9)->nullable()->after('start_point');
            $table->string('start_point_reference')->nullable()->after('start_point_cep');
            $table->decimal('start_point_lat', 10, 7)->nullable()->after('start_point_reference');
            $table->decimal('start_point_lng', 10, 7)->nullable()->after('start_point_lat');
            $table->decimal('end_point_lat', 10, 7)->nullable()->after('end_point');
            $table->decimal('end_point_lng', 10, 7)->nullable()->after('end_point_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn([
                'start_point_cep',
                'start_point_reference',
                'start_point_lat',
                'start_point_lng',
                'end_point_lat',
                'end_point_lng',
            ]);
        });
    }
};
