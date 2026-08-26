<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->text('objeto')->nullable()->after('terms');           // objeto del contrato
            $table->text('product_details')->nullable()->after('objeto');  // detalle de la(s) prenda(s)
            $table->decimal('combo_value', 12, 2)->nullable()->after('product_details');
            $table->decimal('contract_total', 12, 2)->nullable()->after('combo_value');
            $table->string('total_words')->nullable()->after('contract_total'); // valor en letras
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['objeto', 'product_details', 'combo_value', 'contract_total', 'total_words']);
        });
    }
};
