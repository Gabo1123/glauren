<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_post_id')->index();
            $table->foreignId('student_id')->index();
            $table->timestamps();
            $table->unique(['student_post_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_post_likes');
    }
};
