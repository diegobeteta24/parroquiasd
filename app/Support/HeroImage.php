<?php

namespace App\Support;

use Illuminate\Support\Collection;

class HeroImage
{
    protected static array $assetCache = [];
    protected static array $globCache = [];

    public static function resolve(array $options = []): array
    {
        $config = config('portal.hero', []);
        $timezone = $options['timezone'] ?? config('app.timezone');
        $forceSeason = $options['force_season'] ?? null;
        $now = now()->timezone($timezone);

        $seasonalConfig = $config['seasonal'] ?? [];
        $seasonKey = $forceSeason ?? static::detectSeasonKey((int) $now->month, $seasonalConfig);
        $season = $seasonKey ? static::seasonPayload($seasonKey, $seasonalConfig[$seasonKey] ?? []) : null;

        $heroWebp = static::assetIfExists('images/hero.webp');
        $heroJpg = static::assetIfExists('images/hero.jpg');
        $banner = static::assetIfExists('images/banner.webp') ?? static::assetIfExists('images/banner.jpg');
        $fallbackSequence = array_merge(
            $config['primary'] ?? [],
            $config['secondary'] ?? []
        );
        $defaultCandidate = static::pickFirstExisting($fallbackSequence);

        $localBase = $season['primary'] ?? $heroWebp ?? $heroJpg ?? $banner ?? $defaultCandidate;
        $final = $localBase ?? ($config['fallback_remote'] ?? null);

        return [
            'season_key' => $season['key'] ?? null,
            'is_seasonal' => (bool) ($season['key'] ?? false),
            'hero_webp' => $heroWebp,
            'hero_jpg' => $heroJpg,
            'banner' => $banner,
            'local_base' => $localBase,
            'final' => $final,
            'preload' => $localBase,
            'gallery' => $season['gallery'] ?? collect(),
        ];
    }

    protected static function detectSeasonKey(int $currentMonth, array $seasonal): ?string
    {
        foreach ($seasonal as $key => $definition) {
            $months = $definition['months'] ?? [];
            if (in_array($currentMonth, $months, true)) {
                return $key;
            }
        }

        return null;
    }

    protected static function seasonPayload(string $key, array $definition): ?array
    {
        if (empty($definition)) {
            return null;
        }

        return [
            'key' => $key,
            'primary' => static::pickFirstExisting($definition['candidates'] ?? []),
            'gallery' => static::gatherFromGlob($definition['gallery_glob'] ?? null),
        ];
    }

    protected static function pickFirstExisting(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $asset = static::assetIfExists($candidate);
            if ($asset) {
                return $asset;
            }
        }

        return null;
    }

    protected static function assetIfExists(string $relativePath): ?string
    {
        if (array_key_exists($relativePath, static::$assetCache)) {
            return static::$assetCache[$relativePath];
        }

        $fullPath = public_path($relativePath);
        $exists = file_exists($fullPath);
        static::$assetCache[$relativePath] = $exists ? asset($relativePath) : null;

        return static::$assetCache[$relativePath];
    }

    protected static function gatherFromGlob(?string $pattern): Collection
    {
        if (!$pattern) {
            return collect();
        }

        if (array_key_exists($pattern, static::$globCache)) {
            return static::$globCache[$pattern];
        }

        $files = glob(public_path($pattern));
        $collection = collect($files ?: [])->map(function ($path) {
            $relative = ltrim(str_replace(public_path(), '', $path), '/');
            return asset($relative);
        })->values();

        return static::$globCache[$pattern] = $collection;
    }
}
