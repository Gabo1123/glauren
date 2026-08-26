<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // 'estudiante' = cada alumno paga su parte · 'colegio' = el colegio paga todo
            $table->string('payment_mode')->default('estudiante')->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }
};
