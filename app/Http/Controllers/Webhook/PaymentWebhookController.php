<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Webhook\InboundWebhookProcessor;
use Illuminate\Http\Request;

/**
 * SATU controller untuk 3 gateway — semua lewat InboundWebhookProcessor:
 * signature verify -> idempotency (DB unique) -> proses -> retry queue -> dead letter.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(protected InboundWebhookProcessor $processor) {}

    public function duitku(Request $request) { return $this->respond('duitku', $request); }
    public function xendit(Request $request) { return $this->respond('xendit', $request); }
    public function moota(Request $request)  { return $this->respond('moota', $request); }

    protected function respond(string $gateway, Request $request)
    {
        $result = $this->processor->handle($gateway, $request);
        return response()->json($result['body'], $result['status']);
    }
}
