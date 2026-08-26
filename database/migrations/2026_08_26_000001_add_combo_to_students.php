<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Combo de prendas elegido (ej: chaqueta, chaqueta_buzo, chaqueta_buzo_polo)
            $table->string('combo', 40)->nullable()->after('size');
            // Medidas de prendas adicionales (buzo, polo): {buzo:{...}, polo:{...}}
            $table->json('measurements')->nullable()->after('combo');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['combo', 'measurements']);
        });
    }
};
