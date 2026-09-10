<?php

namespace App\View\Composers;

use App\Models\Currency;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StoreMenuComposer
{
    private static ?bool $hasMenusTable = null;
    private static ?bool $hasCurrenciesTable = null;
    private static mixed $cachedHeaderMenu = null;
    private static mixed $cachedCurrencies = null;

    public function compose(View $view)
    {
        if (self::$hasMenusTable === null) {
            self::$hasMenusTable = Cache::rememberForever('schema_has_table_menus', function () {
                try {
                    return Schema::hasTable('menus');
                } catch (\Throwable $e) {
                    return false;
                }
            });
        }

        if (self::$hasMenusTable) {
            if (self::$cachedHeaderMenu === null) {
                self::$cachedHeaderMenu = Cache::remember('storefront_header_menu', 86400, function () {
                    try {
                        return Menu::where('status', 1)
                            ->with([
                                'menuItems' => function ($query) {
                                    $query->orderBy('order_number', 'asc');
                                },
                            ])
                            ->first();
                    } catch (\Throwable $e) {
                        return null;
                    }
                });
            }

            $view->with('headerMenu', self::$cachedHeaderMenu);
        }

        // Share currency options for the header currency switcher.
        if (self::$hasCurrenciesTable === null) {
            self::$hasCurrenciesTable = Cache::rememberForever('schema_has_table_currencies', function () {
                try {
                    return Schema::hasTable('currencies');
                } catch (\Throwable $e) {
                    return false;
                }
            });
        }

        if (self::$hasCurrenciesTable) {
            if (self::$cachedCurrencies === null) {
                self::$cachedCurrencies = Cache::remember('storefront_currencies', 86400, function () {
                    try {
                        return Currency::orderBy('code')->get();
                    } catch (\Throwable $e) {
                        return collect();
                    }
                });
            }

            $view->with('storeCurrencies', self::$cachedCurrencies);
            $view->with('activeCurrencyCode', session('currency', getWebConfig('default_currency', 'USD')));
        }
    }
}
