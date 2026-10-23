<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }
        $exists = DB::table('menu_items')->where('url', '/fotografia-de-grados')->exists();
        if (! $exists) {
            $maxSort = (int) DB::table('menu_items')->max('sort');
            DB::table('menu_items')->insert([
                'label'      => 'Fotografía de Grados',
                'url'        => '/fotografia-de-grados',
                'parent_id'  => null,
                'sort'       => $maxSort + 1,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')->where('url', '/fotografia-de-grados')->delete();
        }
    }
};
