<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Сырой слой импорта: КАЖДАЯ операция выписки (все 47), с категорией и флагом
 * is_revenue. Доменные оплаты (payments) строятся только из is_revenue = true.
 * Остальное хранится для прозрачности («отфильтровано N небизнесовых операций»).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_operations', function (Blueprint $table) {
            $table->id();
            $table->date('op_date');
            $table->string('direction');                  // OperationDirection
            $table->decimal('amount', 15, 2);
            $table->string('doc_number')->nullable();
            $table->string('counterparty_name')->nullable();
            $table->string('counterparty_inn')->nullable();
            $table->string('counterparty_account')->nullable();
            $table->text('purpose');
            $table->string('category');                   // OperationCategory
            $table->boolean('is_revenue')->default(false);
            $table->timestamps();

            $table->index('op_date');
            $table->index('category');
            $table->index('is_revenue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_operations');
    }
};
