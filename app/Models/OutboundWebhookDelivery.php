<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundWebhookDelivery extends Model
{
    protected $fillable = ["subscription_id", "event", "payload", "attempt_count", "status", "response_code", "last_error", "next_retry_at"];
    protected $casts = ["payload" => "array", "next_retry_at" => "datetime"];
    public function subscription() { return $this->belongsTo(OutboundWebhookSubscription::class, "subscription_id"); }
}
