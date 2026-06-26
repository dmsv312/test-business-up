<?php

namespace App\Http\Resources\Concerns;

use App\Domain\Enums\ActStatus;
use App\Models\Payment;
use Illuminate\Support\Collection;

/**
 * Агрегаты по набору оплат для ресурсов клиента и проекта: сумма и разбивка
 * актов по закрытости. Ожидает, что у оплат загружена связь act.
 *
 * @phpstan-param Collection<int, Payment> $payments
 */
trait AggregatesPayments
{
    protected function totalAmount(Collection $payments): string
    {
        $cents = $payments->sum(fn (Payment $p) => (int) round(((float) $p->amount) * 100));

        return number_format($cents / 100, 2, '.', '');
    }

    protected function actsBreakdown(Collection $payments): array
    {
        return [
            'closed' => $payments->filter(fn (Payment $p) => $p->act?->status === ActStatus::Closed)->count(),
            'open' => $payments->filter(fn (Payment $p) => $p->act && $p->act->status !== ActStatus::Closed)->count(),
            'needs_attention' => $payments->filter(fn (Payment $p) => $p->act?->status === ActStatus::NeedsAttention)->count(),
        ];
    }
}
