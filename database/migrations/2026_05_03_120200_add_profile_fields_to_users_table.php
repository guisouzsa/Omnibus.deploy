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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('password');
            $table->string('phone')->nullable()->after('profile_photo');
            $table->text('bio')->nullable()->after('phone');
            $table->string('institution')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['profile_photo', 'phone', 'bio'];
            if (Schema::hasColumn('users', 'institution')) {
                $columns[] = 'institution';
            }
            $table->dropColumn($columns);
        });
    }
};
