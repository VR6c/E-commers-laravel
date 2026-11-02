<?php

namespace App\View\Composers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StoreMenuComposer
{
    private static mixed $cachedHeaderMenu = null;

    public function compose(View $view)
    {
        if (self::$cachedHeaderMenu === null) {
            self::$cachedHeaderMenu = Cache::remember('storefront_header_menu', 86400, function () {
                try {
                    return Menu::where('status', true)
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

        // Share currency options for the header currency switcher.
        $view->with('storeCurrencies', store_currencies());
        $view->with('activeCurrencyCode', session('currency', getWebConfig('default_currency', 'USD')));
    }
}
