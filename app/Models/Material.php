<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = ["class_id", "title", "type", "content", "sort_order", "is_preview"];
    protected $casts = ["is_preview" => "boolean"];
    public function classRoom() { return $this->belongsTo(ClassRoom::class, "class_id"); }
}
