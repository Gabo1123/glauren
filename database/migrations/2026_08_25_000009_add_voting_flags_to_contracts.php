<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('voting_enabled')->default(true)->after('notes');      // ¿votación activa?
            $table->boolean('voting_show_results')->default(true)->after('voting_enabled'); // ¿mostrar ganadora?
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['voting_enabled', 'voting_show_results']);
        });
    }
};
