<?php

namespace App\Domain\Queries;

use Illuminate\Database\Eloquent\Builder;

/**
 * Применяет фильтры дашборда к запросу оплат. Единая точка для эндпоинта оплат
 * и сервиса итогов — фильтры считаются на стороне БД, одинаково для обоих.
 *
 * Поддерживает: from/to (период), client_id, project_id, direction (направление
 * работ), act_status (статус акта), q (поиск по назначению или названию клиента).
 */
class PaymentFilter
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['from'] ?? null, fn (Builder $q, $v) => $q->whereDate('payment_date', '>=', $v))
            ->when($filters['to'] ?? null, fn (Builder $q, $v) => $q->whereDate('payment_date', '<=', $v))
            ->when($filters['client_id'] ?? null, fn (Builder $q, $v) => $q->where('client_id', $v))
            ->when($filters['project_id'] ?? null, fn (Builder $q, $v) => $q->where('project_id', $v))
            ->when($filters['direction'] ?? null, fn (Builder $q, $v) => $q->where('work_direction', $v))
            ->when($filters['act_status'] ?? null, fn (Builder $q, $v) => $q->whereHas('act', fn (Builder $a) => $a->where('status', $v)))
            ->when($filters['q'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $w) => $w
                ->where('payment_purpose', 'like', "%{$v}%")
                ->orWhereHas('client', fn (Builder $c) => $c->where('name', 'like', "%{$v}%"))));
    }
}
