<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBump extends Model
{
    protected $fillable = ["product_id", "bump_product_id", "bump_price", "headline", "active"];
    protected $casts = ["active" => "boolean", "bump_price" => "integer"];
    public function bumpProduct() { return $this->belongsTo(Product::class, "bump_product_id"); }
}
