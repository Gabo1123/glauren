<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->text('description')->nullable()->after('slug');
        });

        Schema::create('collection_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('path');        // ruta en storage o URL completa
            $table->string('caption')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_images');
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description']);
        });
    }
};
