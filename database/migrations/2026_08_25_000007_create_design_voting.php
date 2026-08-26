<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->index();
            $table->string('label')->nullable();
            $table->string('image_path');
            $table->integer('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('design_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->index();
            $table->foreignId('design_option_id')->index();
            $table->foreignId('student_id')->index();
            $table->timestamps();
            $table->unique(['contract_id', 'student_id']); // un voto por estudiante y contrato
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_votes');
        Schema::dropIfExists('design_options');
    }
};
