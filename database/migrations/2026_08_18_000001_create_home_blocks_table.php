<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('texto');       // texto | imagen_texto | cta | video | html
            $table->string('position')->default('before_gallery'); // after_hero | before_gallery | before_footer
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('theme')->default('dark');        // dark | light | gold
            $table->string('align')->default('center');      // left | center
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_blocks');
    }
};
