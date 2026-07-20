<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSequenceEnrollment extends Model
{
    protected $fillable = ["email_sequence_id", "email", "current_step", "next_send_at", "unsubscribed_at"];
    protected $casts = ["next_send_at" => "datetime", "unsubscribed_at" => "datetime"];

    public function sequence() { return $this->belongsTo(EmailSequence::class, 'email_sequence_id'); }
}
