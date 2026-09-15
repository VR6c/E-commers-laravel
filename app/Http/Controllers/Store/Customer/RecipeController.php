<?php

namespace App\Http\Controllers\Store\Customer;

use App\Http\Controllers\Controller;
use App\Services\Store\RecipeAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    protected RecipeAccessService $accessService;

    public function __construct(RecipeAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * Display all recipes unlocked by the customer.
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $recipes = $this->accessService->getUnlockedRecipesForCustomer($customer);
        $currency = activeCurrency();

        return view('themes.xylo.customer.recipes.index', compact('customer', 'recipes', 'currency'));
    }
}
