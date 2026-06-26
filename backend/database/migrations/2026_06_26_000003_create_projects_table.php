<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Проект = тело работ по клиенту (одна запись на клиента). Группирует все его
 * оплаты и этапы. Направление работ и № договора — атрибуты оплаты, т.к. один
 * проект может охватывать разные услуги (реклама, SEO, разработка и т.д.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');     // ProjectStatus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
