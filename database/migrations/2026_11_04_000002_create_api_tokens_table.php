<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Sesiones de la app móvil (equipo, colegios y estudiantes). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 20);              // user | student
            $table->unsignedBigInteger('owner_id');
            $table->string('name', 120)->nullable();         // dispositivo
            $table->string('token', 64)->unique();           // sha256 del token
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
