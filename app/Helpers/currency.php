<?php

use App\Models\Currency;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('convert_price')) {
    function convert_price($amount, $currencyCode = null)
    {
        if (!$amount) return 0;

        static $rates = [];
        static $defaultCurrency = null;

        if ($defaultCurrency === null) {
            $defaultCurrency = getWebConfig('default_currency', 'USD');
        }

        try {
            $currencyCode = $currencyCode ?: (session()->has('currency') ? session('currency') : $defaultCurrency);
        } catch (\Throwable $e) {
            $currencyCode = $currencyCode ?: $defaultCurrency;
        }

        if (!isset($rates['USD'])) {
            $rates['USD'] = Cache::rememberForever('currency_USD', function () {
                try {
                    return Currency::where('code', 'USD')->value('exchange_rate') ?: 1.0;
                } catch (\Throwable $e) {
                    return 1.0;
                }
            });
        }

        if (!isset($rates[$currencyCode])) {
            $rates[$currencyCode] = Cache::rememberForever("currency_{$currencyCode}", function () use ($currencyCode) {
                try {
                    return Currency::where('code', $currencyCode)->value('exchange_rate') ?: 1.0;
                } catch (\Throwable $e) {
                    return 1.0;
                }
            });
        }

        $usdExchangeRate = $rates['USD'] ?: 1.0;
        $targetExchangeRate = $rates[$currencyCode] ?: 1.0;

        if ($usdExchangeRate == $targetExchangeRate) {
            return (float) $amount;
        }

        return round($amount * ($targetExchangeRate / $usdExchangeRate), 2);
    }
}

if (! function_exists('currency_to_usd')) {
    function currency_to_usd($amount, $fromCurrency)
    {
        $usdRate = Currency::where('code', 'USD')->value('exchange_rate') ?: 1.0;
        $fromRate = Currency::where('code', $fromCurrency)->value('exchange_rate') ?: 1.0;

        return round($amount * ($usdRate / $fromRate), 2);
    }
}

if (! function_exists('getWebConfig')) {
    function getWebConfig($key, $default = null)
    {
        static $configs = [];

        if (array_key_exists($key, $configs)) {
            return $configs[$key];
        }

        return $configs[$key] = Cache::rememberForever("store_setting_{$key}", function () use ($key, $default) {
            try {
                return StoreSetting::where('key', $key)->value('value') ?? $default;
            } catch (\Throwable $e) {
                return $default;
            }
        });
    }
}

if (! function_exists('activeCurrency')) {
    function activeCurrency()
    {
        $code = session('currency', 'USD');

        $currency = Cache::rememberForever('active_currency_' . $code, function () use ($code) {
            return Currency::where('code', $code)->first()
                ?? Currency::first();
        });
        return $currency ?? (object) [
            'symbol'        => '$',
            'code'          => 'USD',
            'name'          => 'US Dollar',
            'exchange_rate' => 1.0,
        ];
    }
}

if (! function_exists('getSiteSettings')) {
    function getSiteSettings()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        return $cached = Cache::remember('store_site_settings', 86400, function () {
            try {
                return \App\Models\SiteSetting::first();
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}

if (! function_exists('getSiteLogo')) {
    function getSiteLogo(): string
    {
        $setting = getSiteSettings();
        $logo = $setting?->logo ?: 'logo_icon/shopping.png';
        if (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://'])) {
            return $logo;
        }
        return asset('storage/' . $logo);
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url(?string $imageUrl): string
    {
        if (empty($imageUrl)) {
            return asset('images/no-product.png');
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        // If an optimized .webp version exists on disk, serve the lighter WebP format
        $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $imageUrl);
        if ($webpPath !== $imageUrl) {
            static $checkedWebp = [];
            if (!isset($checkedWebp[$webpPath])) {
                $diskPath = public_path('storage/' . $webpPath);
                $checkedWebp[$webpPath] = file_exists($diskPath);
            }
            if ($checkedWebp[$webpPath]) {
                return \Illuminate\Support\Facades\Storage::url($webpPath);
            }
        }

        return \Illuminate\Support\Facades\Storage::url($imageUrl);
    }
}

if (! function_exists('optimized_image_url')) {
    function optimized_image_url(?string $path, ?string $fallback = null): string
    {
        if (empty($path)) {
            return $fallback ?: asset('images/no-product.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);
        if ($webpPath !== $path) {
            static $checked = [];
            if (!isset($checked[$webpPath])) {
                $checked[$webpPath] = file_exists(public_path('storage/' . $webpPath));
            }
            if ($checked[$webpPath]) {
                return \Illuminate\Support\Facades\Storage::url($webpPath);
            }
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }
}

