<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->string('img_pos')->default('left')->after('spacing'); // left | right (para imagen+texto)
        });
    }

    public function down(): void
    {
        Schema::table('home_blocks', function (Blueprint $table) {
            $table->dropColumn('img_pos');
        });
    }
};
