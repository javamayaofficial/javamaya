<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    protected $fillable = ["affiliate_id", "bank_account_id", "amount", "status", "proof_path", "paid_at"];
    protected $casts = ["paid_at" => "datetime", "amount" => "integer"];
    public function affiliate() { return $this->belongsTo(Affiliate::class); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
}
