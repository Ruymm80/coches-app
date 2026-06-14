<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coche_id')->constrained('coches')->cascadeOnDelete();
            $table->string('path')->nullable();           // opcional: URL externa de fallback
            $table->string('mime_type')->nullable();      // p.ej. image/jpeg
            $table->binary('data')->nullable();           // se promueve a MEDIUMBLOB justo después
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['coche_id', 'sort_order']);
        });

        // MySQL: BLOB sólo guarda 64 KB. Lo promovemos a MEDIUMBLOB (~16 MB).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE imagenes MODIFY data MEDIUMBLOB NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes');
    }
};
