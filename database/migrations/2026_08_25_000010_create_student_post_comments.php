<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_post_id')->index();
            $table->foreignId('student_id')->index();
            $table->string('body', 400);
            $table->boolean('hidden')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_post_comments');
    }
};
