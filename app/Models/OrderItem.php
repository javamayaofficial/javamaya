<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ["order_id", "product_id", "product_name", "price", "is_bump"];
    protected $casts = ["price" => "integer", "is_bump" => "boolean"];
    public function product() { return $this->belongsTo(Product::class); }
}
