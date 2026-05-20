<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('brand')->index();
            $table->string('model')->index();
            $table->text('description');
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('mileage_km');
            $table->string('fuel_type')->index();
            $table->string('transmission')->index();
            $table->string('body_type')->index();
            $table->string('color')->nullable();
            $table->string('province')->index();
            $table->string('status')->default('draft')->index();
            $table->boolean('featured')->default(false)->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['price']);
            $table->index(['year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
