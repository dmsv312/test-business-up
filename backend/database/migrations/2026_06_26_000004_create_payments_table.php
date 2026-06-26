<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Доменная оплата (выручка по проекту) — только из операций с is_revenue = true.
 * Ссылается на породившую её банковскую операцию (bank_operation_id, 1:1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_operation_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->text('payment_purpose');
            $table->string('work_direction')->nullable();   // ProjectDirection (тип услуги)
            $table->string('service_stage')->nullable();    // аванс / этап 1 / финальный платёж
            $table->string('invoice_number')->nullable();
            $table->string('contract_number')->nullable();
            $table->timestamps();

            $table->index('payment_date');
            $table->index('work_direction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
