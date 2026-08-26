<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('slug');
            $table->string('meta_description', 320)->nullable()->after('meta_title');
            $table->string('focus_keyword')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('focus_keyword');
            $table->boolean('noindex')->default(false)->after('og_image');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('slug');
            $table->string('meta_description', 320)->nullable()->after('meta_title');
            $table->string('focus_keyword')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('focus_keyword');
            $table->boolean('noindex')->default(false)->after('og_image');
            $table->string('schema_type')->nullable()->after('noindex'); // WebPage | Product | Service | AboutPage
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'focus_keyword', 'og_image', 'noindex']);
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'focus_keyword', 'og_image', 'noindex', 'schema_type']);
        });
    }
};
