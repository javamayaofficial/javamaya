<?php

use App\Http\Controllers\Cron\CronController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
| Webhook payment: TANPA CSRF & session. Semua lewat InboundWebhookProcessor
| (signature verify -> idempotency DB unique -> proses -> retry -> dead letter).
| URL siap-copy ditampilkan di Admin > API Keys & Webhook URL Overview.
*/
Route::post('/webhooks/duitku', [PaymentWebhookController::class, 'duitku'])->name('webhooks.duitku');
Route::post('/webhooks/xendit', [PaymentWebhookController::class, 'xendit'])->name('webhooks.xendit');
Route::post('/webhooks/moota',  [PaymentWebhookController::class, 'moota'])->name('webhooks.moota');

/* Cron URL opsional: */
Route::get('/cron/run', [CronController::class, 'run'])->name('cron.run');
