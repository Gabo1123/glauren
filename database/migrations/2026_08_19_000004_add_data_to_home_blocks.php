<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->json('data')->nullable()->after('video_url'); // items de módulos (beneficios, pasos, números)
        });
    }

    public function down(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
