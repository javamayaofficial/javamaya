<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLicense extends Model
{
    protected $fillable = ["product_id", "user_id", "order_id", "license_key", "expires_at"];
    protected $casts = ["expires_at" => "datetime"];
}
