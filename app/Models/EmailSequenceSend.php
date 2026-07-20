<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSequenceSend extends Model
{
    protected $fillable = ["email_sequence_enrollment_id", "email_sequence_step_id", "status"];
}
