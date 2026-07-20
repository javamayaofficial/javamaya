<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDeadLetter extends Model
{
    protected $fillable = ["gateway", "payload", "last_error", "attempts", "reprocessed_at"];
    protected $casts = ["payload" => "array", "reprocessed_at" => "datetime"];
}
