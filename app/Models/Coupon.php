<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ["code", "type", "value", "max_uses_total", "max_uses_per_user", "product_ids", "excluded_product_ids", "starts_at", "expires_at", "active"];
    protected $casts = ["product_ids" => "array", "excluded_product_ids" => "array", "starts_at" => "datetime", "expires_at" => "datetime", "active" => "boolean", "value" => "integer"];
    public function usages() { return $this->hasMany(CouponUsage::class); }
}
