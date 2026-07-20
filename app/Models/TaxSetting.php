<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = ["percentage", "mode", "npwp", "store_address"];
    protected $casts = ["percentage" => "decimal:2"];
    public static function current(): self
    {
        return static::query()->first() ?? static::create([]);
    }
}
