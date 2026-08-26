<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_signers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->index();
            $table->string('name');
            $table->string('role')->nullable();      // ej: Padre, Madre, Acudiente
            $table->string('phone')->nullable();     // WhatsApp para el código
            $table->string('email')->nullable();
            $table->string('token', 48)->unique();   // enlace único de firma
            $table->string('otp')->nullable();       // código (hasheado)
            $table->timestamp('otp_expires')->nullable();
            $table->string('signed_document')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_signers');
    }
};
