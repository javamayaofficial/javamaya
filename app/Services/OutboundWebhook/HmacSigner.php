<?php

namespace App\Services\OutboundWebhook;

class HmacSigner
{
    /** Header: X-Javamaya-Signature: sha256=<hmac(rawBody, secret)> */
    public function sign(string $rawBody, string $secret): string
    {
        return 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
    }
}
