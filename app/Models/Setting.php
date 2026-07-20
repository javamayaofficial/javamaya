<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ["key", "value", "encrypted"];
    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(["key" => $key], ["value" => is_scalar($value) || $value === null ? $value : json_encode($value)]);
        cache()->forget("setting.$key");
    }
    public static function get(string $key, mixed $default = null): mixed
    {
        return jm_setting($key, $default);
    }
}
