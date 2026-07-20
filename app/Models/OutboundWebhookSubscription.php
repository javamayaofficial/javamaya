<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundWebhookSubscription extends Model
{
    protected $fillable = ["event", "url", "secret", "active"];
    protected $casts = ["active" => "boolean"];

    public function deliveries() { return $this->hasMany(OutboundWebhookDelivery::class, 'subscription_id'); }
}
