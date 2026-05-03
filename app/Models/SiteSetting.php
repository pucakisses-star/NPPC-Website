<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class SiteSetting extends BaseModel {
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $fillable   = ['key', 'value'];

    /** @var array<string,?string>|null */
    private static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string {
        if (self::$cache === null) {
            self::$cache = static::query()->pluck('value', 'key')->all();
        }

        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        if (self::$cache !== null) {
            self::$cache[$key] = $value;
        }
    }
}
