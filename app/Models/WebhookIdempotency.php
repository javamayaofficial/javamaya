<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookIdempotency extends Model
{
    protected $table = "webhook_idempotency";
    protected $fillable = ["gateway", "event_hash", "processed_at"];
    protected $casts = ["processed_at" => "datetime"];
}
