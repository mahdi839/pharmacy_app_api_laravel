<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    private const CACHE_KEY = 'app_settings.all';

    protected $fillable = ['key', 'value'];

    /**
     * All settings as [key => value], cached until a setting changes.
     *
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()->pluck('value', 'key')->all());
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $values = static::allValues();

        return array_key_exists($key, $values) && $values[$key] !== null ? $values[$key] : $default;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
