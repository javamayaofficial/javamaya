<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ["affiliate_id", "order_id", "amount", "status"];
    protected $casts = ["amount" => "integer"];
    public function affiliate() { return $this->belongsTo(Affiliate::class); }
}
