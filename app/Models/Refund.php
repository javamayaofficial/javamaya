<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = ["order_id", "reason", "amount", "refunded_by", "gateway_reference", "refunded_at"];
    protected $casts = ["refunded_at" => "datetime", "amount" => "integer"];
    public function order() { return $this->belongsTo(Order::class); }
}
