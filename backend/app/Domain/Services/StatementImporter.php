<?php

namespace App\Domain\Services;

use App\Domain\DTO\ParsedOperation;
use App\Domain\Enums\ActStatus;
use App\Domain\Enums\ClientType;
use App\Domain\Enums\ProjectStatus;
use App\Models\Act;
use App\Models\BankOperation;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Доменная сборка: превращает плоский список операций выписки в связанные
 * сущности. Все операции попадают в сырой слой (bank_operations); из выручки
 * строятся клиенты, проекты, оплаты и закрывающие акты.
 *
 * Идемпотентность: clients/projects резолвятся через firstOrCreate, поэтому
 * повторный импорт той же выписки не плодит дубли (bank_operations/payments,
 * однако, добавляются заново — повторный импорт предполагает чистую БД).
 */
class StatementImporter
{
    public function __construct(
        private readonly ActStatusService $actStatus = new ActStatusService(),
    ) {
    }

    /** @param list<ParsedOperation> $operations */
    public function import(array $operations): void
    {
        DB::transaction(function () use ($operations) {
            $revenueIndex = 0;

            foreach ($operations as $op) {
                $bankOperation = $this->storeRawOperation($op);

                if (! $op->isRevenue) {
                    continue;
                }

                $client = $this->resolveClient($op);
                $project = $this->resolveProject($client, $op);
                $payment = $this->storePayment($client, $project, $bankOperation, $op);
                $this->seedAct($payment, $revenueIndex++);
            }

            $this->deriveProjectStatuses();
        });
    }

    private function storeRawOperation(ParsedOperation $op): BankOperation
    {
        return BankOperation::create([
            'op_date' => $op->date,
            'direction' => $op->direction,
            'amount' => $op->amount,
            'doc_number' => $op->docNumber,
            'counterparty_name' => $op->counterpartyName,
            'counterparty_inn' => $op->counterpartyInn,
            'counterparty_account' => $op->counterpartyAccount,
            'purpose' => $op->purpose,
            'category' => $op->category,
            'is_revenue' => $op->isRevenue,
        ]);
    }

    private function resolveClient(ParsedOperation $op): Client
    {
        return Client::firstOrCreate(
            ['inn' => $op->counterpartyInn],
            [
                'name' => $op->counterpartyName,
                'type' => $this->clientType($op->counterpartyName),
                'ogrn' => $op->counterpartyOgrn,
                'bank_account' => $op->counterpartyAccount,
                'bank_name' => $op->bankName,
                'bank_bik' => $op->bankBik,
            ],
        );
    }

    /**
     * Проект = клиент + направление работ. Группировка: по № договора, где он
     * есть; иначе по паре (клиент + направление). Несколько оплат с тем же
     * ключом попадают в один проект.
     */
    private function resolveProject(Client $client, ParsedOperation $op): Project
    {
        $name = $this->shortName($op->counterpartyName).' — '.$op->projectDirection->label();

        if ($op->contractNumber !== null) {
            return Project::firstOrCreate(
                ['client_id' => $client->id, 'contract_number' => $op->contractNumber],
                ['name' => $name, 'direction' => $op->projectDirection, 'status' => ProjectStatus::Active],
            );
        }

        return Project::firstOrCreate(
            ['client_id' => $client->id, 'direction' => $op->projectDirection, 'contract_number' => null],
            ['name' => $name, 'status' => ProjectStatus::Active],
        );
    }

    private function storePayment(Client $client, Project $project, BankOperation $bankOperation, ParsedOperation $op): Payment
    {
        return Payment::create([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'bank_operation_id' => $bankOperation->id,
            'payment_date' => $op->date,
            'amount' => $op->amount,
            'payment_purpose' => $op->purpose,
            'service_stage' => $op->serviceStage,
            'invoice_number' => $op->invoiceNumber,
            'contract_number' => $op->contractNumber,
        ]);
    }

    /**
     * Засев закрывающего акта. Данных об актах в выписке нет — статусы
     * моделируем детерминированно (разброс по индексу выручки), чтобы в
     * дашборде присутствовали все четыре статуса. Итоговый статус всегда
     * считает ActStatusService (в т.ч. «требует внимания» по возрасту оплаты).
     */
    private function seedAct(Payment $payment, int $revenueIndex): void
    {
        [$isSent, $isSigned] = match ($revenueIndex % 4) {
            0, 3 => [true, true],    // закрыт
            1 => [true, false],      // отправлен, не подписан
            2 => [false, false],     // не отправлен
        };

        $paymentDate = CarbonImmutable::parse($payment->payment_date);
        $reference = CarbonImmutable::parse(config('dashboard.reference_date'));
        $sentAt = $isSent ? $paymentDate->addDays(3)->min($reference) : null;
        $signedAt = $isSigned ? $paymentDate->addDays(7)->min($reference) : null;

        $status = $this->actStatus->compute($isSent, $isSigned, $paymentDate);

        Act::create([
            'payment_id' => $payment->id,
            'is_sent' => $isSent,
            'sent_at' => $sentAt,
            'is_signed' => $isSigned,
            'signed_at' => $signedAt,
            'status' => $status,
            'manager_comment' => $status === ActStatus::NeedsAttention
                ? 'Акт не закрыт более '.config('dashboard.act_attention_days').' дней — связаться с клиентом.'
                : null,
        ]);
    }

    /** Проект закрыт, когда по всем его оплатам акты подписаны. */
    private function deriveProjectStatuses(): void
    {
        Project::with('payments.act')->get()->each(function (Project $project) {
            $payments = $project->payments;
            $allClosed = $payments->isNotEmpty()
                && $payments->every(fn (Payment $p) => $p->act?->status === ActStatus::Closed);

            $project->update([
                'status' => $allClosed ? ProjectStatus::Closed : ProjectStatus::Active,
            ]);
        });
    }

    private function clientType(string $name): ClientType
    {
        return match (true) {
            str_starts_with($name, 'ООО') => ClientType::Company,
            str_starts_with($name, 'АНО') => ClientType::NonProfit,
            str_starts_with($name, 'АО') => ClientType::JointStock,
            str_starts_with($name, 'ИП') => ClientType::Entrepreneur,
            default => ClientType::Company,
        };
    }

    /** «ООО "ЛЕДНИК-СТАРТ"» → «Ледник-Старт» для человекочитаемого имени проекта. */
    private function shortName(string $full): string
    {
        $core = preg_replace('/^(ООО|АО|АНО|ИП|ПАО)\s+/u', '', $full);
        $core = trim($core, " \"«»");

        return mb_convert_case(mb_strtolower($core), MB_CASE_TITLE, 'UTF-8');
    }
}
