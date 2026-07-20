<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ["user_id", "action", "subject_type", "subject_id", "meta", "ip"];
    protected $casts = ["meta" => "array"];
    public static function record(string $action, $subject = null, array $meta = []): void
    {
        static::create([
            "user_id" => auth()->id(),
            "action" => $action,
            "subject_type" => $subject ? get_class($subject) : null,
            "subject_id" => $subject?->getKey(),
            "meta" => $meta,
            "ip" => request()?->ip(),
        ]);
    }
}
