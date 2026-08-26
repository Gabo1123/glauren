<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('ai_paused')->default(false)->after('source');       // IA pausada (atiende un humano)
            $table->timestamp('human_requested_at')->nullable()->after('ai_paused'); // cuándo pidió asesor
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['ai_paused', 'human_requested_at']);
        });
    }
};
