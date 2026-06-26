<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Проект = клиент + направление работ. Группирует несколько оплат/этапов.
 * Выводится из выписки: по № договора, где он есть; иначе по паре
 * (клиент + направление из назначения платежа).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('direction');                    // ProjectDirection
            $table->string('contract_number')->nullable();
            $table->string('status')->default('active');     // ProjectStatus
            $table->timestamps();

            $table->index('direction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
