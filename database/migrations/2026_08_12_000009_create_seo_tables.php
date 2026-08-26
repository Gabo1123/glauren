<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Meta SEO por página (clave = home, personalizar, seguimiento, colegios, noticias, o slug de página)
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('label')->nullable();       // nombre legible en el admin
            $table->string('title')->nullable();
            $table->string('description', 320)->nullable();
            $table->string('keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('noindex')->default(false);
            $table->timestamps();
        });

        // Preguntas frecuentes (FAQ + FAQPage schema)
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->longText('answer');
            $table->string('page_key')->default('home');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('seo_pages');
    }
};
