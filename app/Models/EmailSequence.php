<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSequence extends Model
{
    protected $fillable = ["name", "trigger_type", "trigger_id", "active"];
    protected $casts = ["active" => "boolean"];
    public function steps() { return $this->hasMany(EmailSequenceStep::class)->orderBy("sort_order"); }
}
