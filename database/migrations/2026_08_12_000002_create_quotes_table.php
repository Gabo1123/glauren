<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jacket_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('school')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->json('config')->nullable();          // colores, parches, etc.
            $table->decimal('estimated_price', 12, 2)->default(0);
            $table->string('image_path')->nullable();     // captura del diseño
            $table->string('status', 20)->default('nuevo');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
