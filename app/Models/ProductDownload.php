<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDownload extends Model
{
    protected $fillable = ["product_id", "label", "file_path", "file_size"];
    public function product() { return $this->belongsTo(Product::class); }
}
