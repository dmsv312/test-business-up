<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\DashboardSummaryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardFilterRequest;

class DashboardController extends Controller
{
    public function summary(DashboardFilterRequest $request, DashboardSummaryService $service): array
    {
        return ['data' => $service->build($request->filters())];
    }
}
