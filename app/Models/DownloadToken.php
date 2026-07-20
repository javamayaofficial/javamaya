<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadToken extends Model
{
    protected $fillable = ["token", "user_id", "product_download_id", "expires_at", "used_at"];
    protected $casts = ["expires_at" => "datetime", "used_at" => "datetime"];
    public function file() { return $this->belongsTo(ProductDownload::class, "product_download_id"); }
}
