<?php

use App\Http\Controllers\Api\ActController;
use App\Http\Controllers\Api\BankOperationController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

/*
 * REST API дашборда (без авторизации). Контракт — docs/API.md.
 */
Route::get('dashboard/summary', [DashboardController::class, 'summary']);
Route::get('clients', [ClientController::class, 'index']);
Route::get('projects', [ProjectController::class, 'index']);
Route::get('payments', [PaymentController::class, 'index']);
Route::patch('acts/{act}', [ActController::class, 'update']);
Route::get('bank-operations', [BankOperationController::class, 'index']);
