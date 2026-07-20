<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ["order_id", "gateway_code", "gateway_reference", "amount", "status", "raw_payload"];
    protected $casts = ["raw_payload" => "array", "amount" => "integer"];
    public function order() { return $this->belongsTo(Order::class); }
}
