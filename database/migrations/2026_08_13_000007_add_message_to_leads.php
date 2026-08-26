<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('school')->nullable()->after('phone');
            $table->text('message')->nullable()->after('school');
            $table->string('source')->nullable()->default('web')->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['school', 'message', 'source']);
        });
    }
};
