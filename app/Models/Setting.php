<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * Static in-memory cache to avoid repeated DB queries within the same request.
     */
    protected static array $cache = [];

    /**
     * Get a setting value by key.
     * Uses in-memory cache to prevent N+1 queries on pages with many setting reads.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key] ?? $default;
        }

        $setting = static::where('key', $key)->first();
        if (!$setting) {
            // Cache the miss too, so we don't re-query for absent keys
            static::$cache[$key] = null;
            return $default;
        }

        $value = match ($setting->type) {
            'integer', 'int' => (int) $setting->value,
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true) ?? $default,
            default => $setting->value,
        };

        static::$cache[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value by key.
     * Invalidates the in-memory cache for this key so the next read is fresh.
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        if (is_bool($value)) {
            $type = 'boolean';
            $formattedValue = $value ? 'true' : 'false';
        } elseif (is_int($value)) {
            $type = 'integer';
            $formattedValue = (string) $value;
        } elseif (is_array($value)) {
            $type = 'json';
            $formattedValue = json_encode($value);
        } else {
            $formattedValue = (string) $value;
        }

        // Invalidate cache for this key
        unset(static::$cache[$key]);

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $formattedValue, 'type' => $type, 'group' => $group]
        );
    }

    /**
     * Flush the entire in-memory cache (useful for tests or after bulk updates).
     */
    public static function flushCache(): void
    {
        static::$cache = [];
    }
}
