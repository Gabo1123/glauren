<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('category')->default('otros'); // tela, insumos, mano_obra, transporte, servicios, otros
            $table->string('concept');                     // qué se compró
            $table->string('supplier')->nullable();        // proveedor
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('invoice_path')->nullable();    // foto o PDF de la factura
            // A quién/qué se asocia (todo opcional)
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('jacket_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material')->nullable();         // ej. "Tela cuerpo"
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
