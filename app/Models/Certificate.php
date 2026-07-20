<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = ["user_id", "class_id", "code", "participant_name", "pdf_path", "issued_at"];
    protected $casts = ["issued_at" => "datetime"];
    public function user() { return $this->belongsTo(User::class); }
    public function classRoom() { return $this->belongsTo(ClassRoom::class, "class_id"); }
}
