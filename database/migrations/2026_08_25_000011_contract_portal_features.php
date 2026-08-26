<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('design_approved_at')->nullable()->after('signed_at');
            $table->string('join_token', 40)->nullable()->unique()->after('sign_token');
            $table->date('delivery_date')->nullable()->after('design_approved_at'); // fecha comprometida de entrega
        });

        // token de registro para el QR
        \App\Modules\Chaquetas\Models\Contract::whereNull('join_token')->get()
            ->each(fn ($c) => $c->update(['join_token' => Str::random(20)]));

        Schema::create('contract_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount_delta', 12, 2)->default(0); // + o - sobre el total
            $table->string('status')->default('pendiente');    // pendiente | aprobado | rechazado
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_changes');
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['design_approved_at', 'join_token', 'delivery_date']);
        });
    }
};
