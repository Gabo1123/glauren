<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->string('phase', 20)->default('proceso');  // proceso | final
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_photos');
    }
};
