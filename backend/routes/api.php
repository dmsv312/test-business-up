<?php

use Illuminate\Support\Facades\Route;

/*
 * REST API дашборда. Полный набор эндпоинтов (dashboard/summary, clients,
 * projects, payments, acts) добавляется на этапе API. Пока — smoke-проверка.
 */
Route::get('/ping', fn () => ['ok' => true, 'service' => 'payments-dashboard']);
