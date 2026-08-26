<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            // null = bloque del home; con valor = bloque de esa página (/p/slug)
            $table->foreignId('page_id')->nullable()->after('id')->constrained('pages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('page_id');
        });
    }
};
