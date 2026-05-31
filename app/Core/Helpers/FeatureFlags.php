<?php

namespace App\Core\Helpers;

use App\Shared\Models\User;
use Illuminate\Support\Facades\Config;

class FeatureFlags
{
    public static function isEnabled(string $feature): bool
    {
        return Config::get("features.{$feature}", false);
    }

    public static function isDisabled(string $feature): bool
    {
        return !self::isEnabled($feature);
    }

    public static function enableFor(string $feature, User $user): bool
    {
        return self::isEnabled($feature);
    }

    public static function disableFor(string $feature, User $user): bool
    {
        return !self::enableFor($feature, $user);
    }

    public static function requireFeature(string $feature): void
    {
        if (!self::isEnabled($feature)) {
            abort(404, "Feature '{$feature}' is not available");
        }
    }

    public static function availableFeatures(): array
    {
        return Config::get('features', []);
    }
}
