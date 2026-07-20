<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadMagnet extends Model
{
    protected $fillable = ["title", "slug", "deliverable_url", "active"];
}
