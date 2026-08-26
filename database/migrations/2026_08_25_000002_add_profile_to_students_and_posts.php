<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('pin')->nullable()->after('phone');   // PIN de acceso (hasheado)
            $table->string('photo')->nullable()->after('pin');   // foto de perfil
            $table->string('bio', 200)->nullable()->after('photo');
        });

        Schema::create('student_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->index();
            $table->foreignId('contract_id')->nullable()->index(); // muro del curso = mismo contrato
            $table->text('body')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('hidden')->default(false); // moderación
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_posts');
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['pin', 'photo', 'bio']);
        });
    }
};
