<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Historial de seguimiento de cada cotización (envíos, vistas, cambios, notas). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);          // creada, whatsapp, correo, vista, estado, nota, pdf
            $table->string('description');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['quote_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_activities');
    }
};
