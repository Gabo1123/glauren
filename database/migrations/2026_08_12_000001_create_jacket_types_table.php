<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jacket_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 16)->default('🧥');
            $table->text('description')->nullable();
            $table->string('model_path')->nullable();   // ruta del .glb en disco 'public'
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('patch_price', 12, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jacket_types');
    }
};
