<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Anticipo del colegio al firmar el contrato. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('deposit_amount', 14, 2)->nullable()->after('contract_total');   // lo que se pide
            $table->decimal('deposit_paid', 14, 2)->nullable()->after('deposit_amount');     // lo que llegó
            $table->timestamp('deposit_paid_at')->nullable()->after('deposit_paid');
            $table->string('deposit_method', 60)->nullable()->after('deposit_paid_at');
            $table->string('deposit_proof')->nullable()->after('deposit_method');            // comprobante que sube el colegio
            $table->timestamp('deposit_declared_at')->nullable()->after('deposit_proof');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'deposit_paid', 'deposit_paid_at', 'deposit_method', 'deposit_proof', 'deposit_declared_at']);
        });
    }
};
