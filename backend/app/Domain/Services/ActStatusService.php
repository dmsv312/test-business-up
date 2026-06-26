<?php

namespace App\Domain\Services;

use App\Domain\Enums\ActStatus;
use App\Models\Act;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Расчёт статуса закрывающего акта — единственный источник правды по статусу.
 *
 * Правила (по ТЗ):
 *   отправлен и подписан                        → «закрыт»
 *   оплата старше N дней и акт не закрыт         → «требует внимания»
 *   отправлен, но не подписан (и не старая)      → «ожидает подписи»
 *   иначе                                        → «не отправлен»
 *
 * «Сейчас» и порог давности берутся из config('dashboard.*): данные выписки
 * датированы будущим, поэтому давность считаем от даты её формирования.
 */
class ActStatusService
{
    private CarbonImmutable $reference;
    private int $attentionDays;

    public function __construct(?string $reference = null, ?int $attentionDays = null)
    {
        $this->reference = CarbonImmutable::parse($reference ?? config('dashboard.reference_date'));
        $this->attentionDays = $attentionDays ?? (int) config('dashboard.act_attention_days');
    }

    public function compute(bool $isSent, bool $isSigned, CarbonInterface $paymentDate): ActStatus
    {
        if ($isSent && $isSigned) {
            return ActStatus::Closed;
        }

        $cutoff = $this->reference->subDays($this->attentionDays);
        if ($paymentDate->lessThanOrEqualTo($cutoff)) {
            return ActStatus::NeedsAttention;
        }

        return $isSent ? ActStatus::AwaitingSignature : ActStatus::NotSent;
    }

    public function for(Act $act): ActStatus
    {
        return $this->compute(
            (bool) $act->is_sent,
            (bool) $act->is_signed,
            $act->payment->payment_date,
        );
    }
}
