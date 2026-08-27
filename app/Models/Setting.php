<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'integer', 'int' => (int) $setting->value,
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true) ?? $default,
            default => $setting->value,
        };
    }

    /**
     * Set a setting value by key.
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

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $formattedValue, 'type' => $type, 'group' => $group]
        );
    }
}
