<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jacket_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();               // ej: CTR-2026-001
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('production_status', 30)->default('cotizado');
            $table->decimal('price_per_unit', 12, 2)->nullable();
            $table->longText('terms')->nullable();           // texto del contrato / condiciones

            // Firma del contrato desde la plataforma
            $table->string('contract_status', 20)->default('borrador'); // borrador|enviado|firmado
            $table->string('sign_token', 64)->nullable()->unique();     // enlace único para firmar
            $table->string('signed_by_name')->nullable();
            $table->string('signed_by_document')->nullable();
            $table->string('signature_path')->nullable();               // imagen de la firma
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
