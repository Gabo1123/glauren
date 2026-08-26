<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->index();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('school')->nullable();
            $table->date('date');
            $table->time('time');
            $table->string('status')->default('agendada'); // agendada | atendida | cancelada
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['date', 'time']); // un solo cupo por franja → desaparece cuando se toma
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
