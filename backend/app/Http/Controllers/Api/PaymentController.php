<?php

namespace App\Http\Controllers\Api;

use App\Domain\Queries\PaymentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardFilterRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function index(DashboardFilterRequest $request, PaymentFilter $filter): AnonymousResourceCollection
    {
        $query = Payment::query()->with(['client', 'project', 'act']);
        $filter->apply($query, $request->filters());

        $payments = $query
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(25);

        return PaymentResource::collection($payments);
    }
}
