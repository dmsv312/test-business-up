<?php

namespace App\Domain\Services;

use App\Domain\Enums\ActStatus;
use App\Domain\Queries\PaymentFilter;
use App\Models\BankOperation;
use App\Models\Payment;

/**
 * Считает сводку дашборда на стороне backend (требование ТЗ: итоги — в отдельном
 * слое логики). Все суммы и счётчики берутся с учётом активных фильтров.
 * Деньги суммируются в копейках, чтобы избежать ошибок плавающей точки.
 */
class DashboardSummaryService
{
    public function __construct(
        private readonly PaymentFilter $filter = new PaymentFilter(),
    ) {
    }

    public function build(array $filters = []): array
    {
        $query = Payment::query()->with('act');
        $this->filter->apply($query, $filters);
        $payments = $query->get();

        $cents = fn (Payment $p): int => (int) round(((float) $p->amount) * 100);
        $rub = fn (int $c): string => number_format($c / 100, 2, '.', '');

        $totalCents = $payments->sum($cents);
        $closedCents = $payments
            ->filter(fn (Payment $p) => $p->act?->status === ActStatus::Closed)
            ->sum($cents);

        return [
            'total_revenue' => $rub($totalCents),
            'clients_count' => $payments->pluck('client_id')->unique()->count(),
            'projects_count' => $payments->pluck('project_id')->unique()->count(),
            'payments_count' => $payments->count(),
            'closed_acts_amount' => $rub($closedCents),
            'open_acts_amount' => $rub($totalCents - $closedCents),
            'payments_without_sent_act' => $payments->filter(fn (Payment $p) => ! $p->act?->is_sent)->count(),
            'payments_sent_not_signed' => $payments->filter(fn (Payment $p) => $p->act?->is_sent && ! $p->act?->is_signed)->count(),
            'acts_needs_attention' => $payments->filter(fn (Payment $p) => $p->act?->status === ActStatus::NeedsAttention)->count(),
            'filtered_out_operations' => $this->filteredOutCount($filters),
        ];
    }

    /** Сколько небизнесовых операций выписки исключено из выручки (за период, если задан). */
    private function filteredOutCount(array $filters): int
    {
        return BankOperation::query()
            ->where('is_revenue', false)
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('op_date', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('op_date', '<=', $v))
            ->count();
    }
}
