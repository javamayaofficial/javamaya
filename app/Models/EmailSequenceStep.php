<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSequenceStep extends Model
{
    protected $fillable = ["email_sequence_id", "delay_hours", "subject", "body", "sort_order"];
}
