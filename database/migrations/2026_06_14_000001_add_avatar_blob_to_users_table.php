<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_mime')->nullable()->after('avatar');
            $table->binary('avatar_data')->nullable()->after('avatar_mime');
        });

        // En MySQL: promovemos avatar_data a MEDIUMBLOB (~16 MB) por si acaso.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY avatar_data MEDIUMBLOB NULL');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_mime', 'avatar_data']);
        });
    }
};
