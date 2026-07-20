<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBundleItem extends Model
{
    protected $fillable = ["product_bundle_id", "item_product_id"];
    public function itemProduct() { return $this->belongsTo(Product::class, "item_product_id"); }
}
