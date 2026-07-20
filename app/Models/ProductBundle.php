<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    protected $fillable = ["product_id"];
    public function product() { return $this->belongsTo(Product::class); }
    public function items() { return $this->hasMany(ProductBundleItem::class); }
}
