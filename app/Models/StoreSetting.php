<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable=['key','value'];
    public static function get(string $key, ?string $fallback=null): ?string
    {
        return static::where('key',$key)->value('value') ?? $fallback;
    }
    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key'=>$key],['value'=>$value]);
    }
}
