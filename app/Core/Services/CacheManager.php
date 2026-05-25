<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Cache;

class CacheManager
{
    private const CACHE_TTL_LIST = 3600;
    private const CACHE_TTL_ITEM = 1800;
    private const MANIFEST_KEY = 'cache_manager:key_registry';

    private static function registerKey(string $cacheKey): void
    {
        $keys = Cache::get(self::MANIFEST_KEY, []);
        if (!in_array($cacheKey, $keys, true)) {
            $keys[] = $cacheKey;
        }
        Cache::forever(self::MANIFEST_KEY, $keys);
    }

    private static function unregisterKey(string $cacheKey): void
    {
        $keys = Cache::get(self::MANIFEST_KEY, []);
        Cache::forever(self::MANIFEST_KEY, array_values(array_diff($keys, [$cacheKey])));
    }

    private static function getRegisteredKeys(): array
    {
        return Cache::get(self::MANIFEST_KEY, []);
    }

    public static function make(string $key, string $context, ?string $id = null, string $operation = 'list', ?array $params = null): string
    {
        $parts = [$key, $context];

        if ($id) {
            $parts[] = $id;
        }

        $parts[] = $operation;

        if ($params) {
            $parts[] = md5(json_encode($params));
        }

        return implode(':', $parts);
    }

    public static function get(string $cacheKey, callable $callback)
    {
        self::registerKey($cacheKey);

        return Cache::remember($cacheKey, self::CACHE_TTL_LIST, $callback);
    }

    public static function getItem(string $cacheKey, callable $callback)
    {
        self::registerKey($cacheKey);

        return Cache::remember($cacheKey, self::CACHE_TTL_ITEM, $callback);
    }

    public static function invalidate(string $model, ?string $context = null, ?string $id = null): void
    {
        if (!$id) {
            Cache::flush();
            Cache::forever(self::MANIFEST_KEY, []);
            return;
        }

        $keys = [
            self::make($model, $context ?? 'all', $id, 'list'),
            self::make($model, $context ?? 'all', $id, 'item'),
            self::make($model, $context ?? 'all', $id),
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
            self::unregisterKey($key);
        }
    }

    public static function invalidatePattern(string $pattern): void
    {
        $keys = self::getRegisteredKeys();

        foreach ($keys as $key) {
            if (str_starts_with($key, $pattern)) {
                Cache::forget($key);
                self::unregisterKey($key);
            }
        }
    }
}
