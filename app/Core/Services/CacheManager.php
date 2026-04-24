<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Cache;

class CacheManager
{
    private const CACHE_TTL_LIST = 3600;
    private const CACHE_TTL_ITEM = 1800;

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
        return Cache::remember($cacheKey, self::CACHE_TTL_LIST, $callback);
    }

    public static function getItem(string $cacheKey, callable $callback)
    {
        return Cache::remember($cacheKey, self::CACHE_TTL_ITEM, $callback);
    }

    public static function invalidate(string $model, ?string $context = null, ?string $id = null): void
    {
        if (!$id) {
            Cache::flush();
            return;
        }

        Cache::forget(self::make($model, $context ?? 'all', $id, 'list'));
        Cache::forget(self::make($model, $context ?? 'all', $id, 'item'));
        Cache::forget(self::make($model, $context ?? 'all', $id));
    }

    public static function invalidatePattern(string $pattern): void
    {
        // For file-based cache
        if (config('cache.default') === 'file') {
            $cacheDir = storage_path('framework/cache');
            if (is_dir($cacheDir)) {
                foreach (glob("{$cacheDir}/*") as $file) {
                    if (is_file($file) && strpos(file_get_contents($file), $pattern) !== false) {
                        unlink($file);
                    }
                }
            }
        }

        // For array/memory cache
        Cache::forget($pattern);
    }
}
