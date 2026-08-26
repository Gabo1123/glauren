<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();

            // Datos que sube el colegio o el admin
            $table->string('document')->index();     // tarjeta de identidad / cédula
            $table->string('name');
            $table->enum('gender', ['hombre', 'mujer'])->nullable();
            $table->string('nickname')->nullable();  // apodo

            // Toma de medidas (solo admin las edita; el colegio las ve)
            $table->string('size')->nullable();               // talla
            $table->decimal('talle_length', 6, 1)->nullable();  // largo de talle
            $table->decimal('sleeve_length', 6, 1)->nullable(); // largo de manga
            $table->decimal('chest', 6, 1)->nullable();         // contorno de pecho
            $table->decimal('waist', 6, 1)->nullable();         // contorno de cintura
            $table->decimal('hip', 6, 1)->nullable();           // contorno de cadera

            // Pago (Rapyd)
            $table->string('payment_status', 20)->default('pendiente'); // pendiente|pagado
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('rapyd_checkout_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->unique(['contract_id', 'document']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
