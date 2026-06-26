<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Закрывающий акт по оплате (1:1). Данных об актах в выписке нет — слой
 * моделируется при сидировании. status кэшируется в БД (для фильтрации),
 * но всегда выводим из is_sent / is_signed / возраста оплаты в ActStatusService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->string('status')->default('not_sent');   // ActStatus
            $table->text('manager_comment')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acts');
    }
};
