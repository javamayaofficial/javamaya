<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    protected $fillable = ["title", "slug", "body", "published", "show_in_footer"];
    protected $casts = ["published" => "boolean", "show_in_footer" => "boolean"];
}
