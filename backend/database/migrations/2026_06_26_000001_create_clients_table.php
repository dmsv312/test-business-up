<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Клиенты / юрлица (плательщики). Реквизиты берутся из стороны плательщика
 * в банковской выписке: наименование, ИНН, ОГРН/ОГРНИП, расчётный счёт, банк.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');                       // App\Domain\Enums\ClientType
            $table->string('inn')->nullable()->unique();  // ключ дедупликации при импорте
            $table->string('ogrn')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_bik')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
