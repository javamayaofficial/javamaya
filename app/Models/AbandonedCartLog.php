<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCartLog extends Model
{
    protected $fillable = ["order_id", "reminders_sent", "last_reminder_at"];
    protected $casts = ["last_reminder_at" => "datetime"];
    public function order() { return $this->belongsTo(Order::class); }
}
