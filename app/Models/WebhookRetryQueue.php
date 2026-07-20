<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookRetryQueue extends Model
{
    protected $table = "webhook_retry_queue";
    protected $fillable = ["gateway", "payload", "event_hash", "attempts", "last_error", "next_retry_at"];
    protected $casts = ["payload" => "array", "next_retry_at" => "datetime"];
}
