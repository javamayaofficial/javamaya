<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastCampaign extends Model
{
    protected $fillable = ["name", "channel", "subject", "body", "segment", "status", "throttle_per_minute"];
    protected $casts = ["segment" => "array"];
    public function recipients() { return $this->hasMany(BroadcastRecipient::class); }
}
