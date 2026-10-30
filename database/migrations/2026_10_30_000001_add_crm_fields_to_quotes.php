<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Cotizaciones como CRM: número consecutivo, enlace público para el cliente,
 * prendas/combos elegidos y seguimiento (enviada, vista, próximo contacto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->unsignedInteger('number')->nullable()->unique()->after('id');
            $table->string('public_token', 40)->nullable()->unique()->after('number');
            $table->json('items')->nullable()->after('config');          // prendas/combos/adicionales elegidos
            $table->string('source', 20)->default('web')->after('status'); // web | manual
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->date('follow_up_at')->nullable()->index();
        });

        // Numera las cotizaciones que ya existen, en orden de llegada.
        $start = (int) (DB::table('site_settings')->where('key', 'quote_number_start')->value('value') ?: 65044);
        $n = $start;
        DB::table('quotes')->orderBy('id')->select('id')->get()->each(function ($q) use (&$n) {
            DB::table('quotes')->where('id', $q->id)->update([
                'number'       => $n++,
                'public_token' => Str::random(32),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['number']);
            $table->dropUnique(['public_token']);
            $table->dropIndex(['follow_up_at']);
            $table->dropColumn(['number', 'public_token', 'items', 'source', 'sent_at', 'viewed_at', 'view_count', 'follow_up_at']);
        });
    }
};
