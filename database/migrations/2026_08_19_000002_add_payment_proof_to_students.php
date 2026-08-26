<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('payment_status');   // efectivo | nequi | bancolombia
            $table->string('payment_proof')->nullable()->after('payment_method');    // comprobante (imagen)
            $table->timestamp('payment_declared_at')->nullable()->after('payment_proof');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_proof', 'payment_declared_at']);
        });
    }
};
