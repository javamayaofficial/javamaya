<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = ["name", "key_hash", "key_prefix", "scopes", "rate_limit_per_minute", "last_used_at", "revoked_at"];
    protected $casts = ["scopes" => "array", "last_used_at" => "datetime", "revoked_at" => "datetime"];
    public function hasScope(string $scope): bool { return in_array($scope, $this->scopes ?? [], true); }
}
