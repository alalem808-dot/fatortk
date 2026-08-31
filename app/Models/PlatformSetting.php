<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value', 'label'];

    /**
     * Get a setting value by key (cached for 1 hour)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('platform_settings', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value and clear cache
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('platform_settings');
    }

    /**
     * Get all settings as key => value array
     */
    public static function all_settings(): array
    {
        return static::orderBy('key')->get()->keyBy('key')->toArray();
    }

    /**
     * Build a WhatsApp URL
     */
    public static function whatsappUrl(string $messageKey = 'whatsapp_subscribe_msg'): string
    {
        $number  = static::get('whatsapp_number', '');
        $message = static::get($messageKey, '');
        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }

    /**
     * Get public URL for a stored image setting
     */
    public static function imageUrl(string $key): ?string
    {
        $path = static::get($key);
        if (!$path) return null;
        return url('storage/' . $path);
    }
}
