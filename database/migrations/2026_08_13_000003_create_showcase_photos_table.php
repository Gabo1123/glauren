<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showcase_photos', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');       // ruta en storage o URL completa
            $table->string('label')->nullable(); // nombre del colegio / etiqueta
            $table->integer('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showcase_photos');
    }
};
