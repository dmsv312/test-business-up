<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankOperationResource;
use App\Models\BankOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Сырой слой выписки (все операции) — для прозрачности классификации
 * («из 47 операций 24 — выручка, остальное отфильтровано»).
 */
class BankOperationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $operations = BankOperation::query()
            ->when($request->query('category'), fn (Builder $q, $v) => $q->where('category', $v))
            ->when($request->query('direction'), fn (Builder $q, $v) => $q->where('direction', $v))
            ->when($request->filled('is_revenue'), fn (Builder $q) => $q->where('is_revenue', $request->boolean('is_revenue')))
            ->when($request->query('from'), fn (Builder $q, $v) => $q->whereDate('op_date', '>=', $v))
            ->when($request->query('to'), fn (Builder $q, $v) => $q->whereDate('op_date', '<=', $v))
            ->when($request->query('q'), fn (Builder $q, $v) => $q->where(fn (Builder $w) => $w
                ->where('purpose', 'like', "%{$v}%")
                ->orWhere('counterparty_name', 'like', "%{$v}%")))
            ->orderBy('op_date')
            ->orderBy('id')
            ->get();

        return BankOperationResource::collection($operations);
    }
}
