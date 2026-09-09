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

if (! function_exists('product_image_url')) {
    function product_image_url(?string $imageUrl): string
    {
        if (empty($imageUrl)) {
            return asset('images/no-product.png');
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        return \Illuminate\Support\Facades\Storage::url($imageUrl);
    }
}
