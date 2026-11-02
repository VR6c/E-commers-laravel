<?php

use App\Models\Currency;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('store_currencies')) {
    /**
     * All currencies, loaded once per request (and cached across requests when the
     * cache store is shared). convert_price(), activeCurrency() and the header
     * currency switcher all read from this single list instead of querying separately.
     */
    function store_currencies()
    {
        static $memo = null;

        // Re-load when the application instance changes (tests, queue workers, Octane)
        if ($memo === null || $memo[0] !== app()) {
            $memo = [app(), Cache::remember('storefront_currencies', 86400, function () {
                try {
                    return Currency::orderBy('code')->get();
                } catch (\Throwable $e) {
                    return collect();
                }
            })];
        }

        return $memo[1];
    }
}

if (! function_exists('convert_price')) {
    function convert_price($amount, $currencyCode = null)
    {
        if (! $amount) {
            return 0;
        }

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

        $rateFor = fn (string $code) => store_currencies()->firstWhere('code', $code)?->exchange_rate ?: 1.0;

        $rates['USD'] ??= $rateFor('USD');
        $rates[$currencyCode] ??= $rateFor($currencyCode);

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

        $currencies = store_currencies();
        $currency = $currencies->firstWhere('code', $code) ?? $currencies->sortBy('id')->first();

        return $currency ?? (object) [
            'symbol' => '$',
            'code' => 'USD',
            'name' => 'US Dollar',
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
        if ($logo === 'logo_icon/shopping.png' && ! file_exists(public_path('storage/'.$logo))) {
            return 'https://i.ibb.co/jkrX65dY/shop.png';
        }

        return asset('storage/'.$logo);
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
            if (! isset($checkedWebp[$webpPath])) {
                $diskPath = public_path('storage/'.$webpPath);
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
            if (! isset($checked[$webpPath])) {
                $checked[$webpPath] = file_exists(public_path('storage/'.$webpPath));
            }
            if ($checked[$webpPath]) {
                return \Illuminate\Support\Facades\Storage::url($webpPath);
            }
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }
}
