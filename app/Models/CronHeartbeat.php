<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronHeartbeat extends Model
{
    protected $fillable = ["source", "last_ran_at", "jobs_processed", "errors_count"];
    protected $casts = ["last_ran_at" => "datetime"];
    public static function beat(string $source, int $jobs = 0, int $errors = 0): void
    {
        static::updateOrCreate(["source" => $source], ["last_ran_at" => now(), "jobs_processed" => $jobs, "errors_count" => $errors]);
    }
    public static function lastRanSecondsAgo(): ?int
    {
        $last = static::query()->max("last_ran_at");
        return $last ? now()->diffInSeconds(\Carbon\Carbon::parse($last)) : null;
    }
}
