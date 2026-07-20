<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelEvent extends Model
{
    protected $fillable = ["stage", "product_id", "session_hash", "event_date"];
    protected $casts = ["event_date" => "date"];
}
