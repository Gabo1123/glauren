<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jacket_types', function (Blueprint $table) {
            // 'modelo' = usa el .glb cargado como editor principal; 'parametrico' = chaqueta por zonas
            $table->string('default_view')->default('modelo')->after('model_path');
        });
    }

    public function down(): void
    {
        Schema::table('jacket_types', function (Blueprint $table) {
            $table->dropColumn('default_view');
        });
    }
};
