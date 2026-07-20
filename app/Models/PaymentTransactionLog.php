<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransactionLog extends Model
{
    protected $fillable = ["order_id", "gateway_code", "direction", "payload", "result"];
    protected $casts = ["payload" => "array"];
}
