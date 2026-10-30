<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Une cada cotización con su lead (contacto) y con el colegio cuando el cliente contrata. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('jacket_type_id')->constrained()->nullOnDelete();
            $table->foreignId('school_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropConstrainedForeignId('lead_id');
        });
    }
};
