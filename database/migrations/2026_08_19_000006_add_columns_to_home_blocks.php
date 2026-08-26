<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->unsignedTinyInteger('columns')->nullable()->after('data'); // 1..6, null = automático
        });
    }

    public function down(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->dropColumn('columns');
        });
    }
};
