<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->string('bg_color')->nullable()->after('theme'); // color de fondo personalizado (hex), opcional
            $table->string('width')->default('normal')->after('bg_color'); // normal | wide | full
        });
    }

    public function down(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'width']);
        });
    }
};
