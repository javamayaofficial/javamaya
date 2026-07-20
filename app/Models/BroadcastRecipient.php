<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastRecipient extends Model
{
    protected $fillable = ["broadcast_campaign_id", "recipient", "status", "error"];
}
