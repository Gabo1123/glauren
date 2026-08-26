<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->foreignId('lead_id')->nullable()->index();
            $table->enum('direction', ['in', 'out']);
            $table->text('body');
            $table->string('wa_id')->nullable();       // id del mensaje en Meta (para no duplicar)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
    }
};
