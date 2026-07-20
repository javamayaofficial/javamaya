<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUpsell extends Model
{
    protected $fillable = ['main_product_id', 'upsell_product_id', 'upsell_price', 'headline', 'description', 'active'];
    protected $casts = ['active' => 'boolean', 'upsell_price' => 'integer'];

    public function mainProduct()   { return $this->belongsTo(Product::class, 'main_product_id'); }
    public function upsellProduct() { return $this->belongsTo(Product::class, 'upsell_product_id'); }
}
