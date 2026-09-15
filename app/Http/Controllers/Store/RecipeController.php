<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Recipe;
use App\Services\Store\RecipeAccessService;
use App\Services\Store\RecipePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    protected RecipeAccessService $accessService;
    protected RecipePdfService $pdfService;

    public function __construct(RecipeAccessService $accessService, RecipePdfService $pdfService)
    {
        $this->accessService = $accessService;
        $this->pdfService = $pdfService;
    }

    /**
     * Browse recipe catalog.
     */
    public function index(Request $request)
    {
        $query = Recipe::active()->with('products');

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('summary', 'like', "%{$keyword}%")
                  ->orWhere('cuisine', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('cuisine')) {
            $query->where('cuisine', $request->cuisine);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $recipes = $query->latest()->paginate(12)->withQueryString();

        // Get list of unlocked recipe IDs for current user / session
        $customer = Auth::guard('sanctum')->user() ?? Auth::guard('customer')->user();
        if (!$customer && $request->bearerToken()) {
            $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            $customer = $pat?->tokenable;
        }
        $unlockedRecipeIds = [];

        if ($customer) {
            $unlockedRecipeIds = $this->accessService->getUnlockedRecipesForCustomer($customer)->pluck('id')->toArray();
        }

        $cuisines = Recipe::active()->whereNotNull('cuisine')->distinct()->pluck('cuisine');
        $currency = activeCurrency();

        return view('themes.xylo.recipes.index', compact('recipes', 'unlockedRecipeIds', 'cuisines', 'currency'));
    }

    /**
     * Display recipe details with gated full content and PDF download.
     */
    public function show(string $slug, Request $request)
    {
        $recipe = Recipe::where('slug', $slug)->with('products.thumbnail')->firstOrFail();

        $customer = Auth::guard('sanctum')->user() ?? Auth::guard('customer')->user();
        if (!$customer && $request->bearerToken()) {
            $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            $customer = $pat?->tokenable;
        }
        $orderId = $request->get('order_id');
        $token = $request->get('token');

        $order = null;
        if ($orderId) {
            $order = Order::with(['details.product'])->find($orderId);
        }

        $isUnlocked = $this->accessService->hasAccess($recipe, $customer, $orderId ? (int)$orderId : null, $token);
        $currency = activeCurrency();

        // Check if there is an associated completed order for the current user
        if (!$order && $customer) {
            $linkedProductIds = $recipe->products->pluck('id')->toArray();
            $order = Order::where('customer_id', $customer->id)
                ->whereIn('status', ['completed', 'processing'])
                ->whereHas('details', fn($q) => $q->whereIn('product_id', $linkedProductIds))
                ->latest()
                ->first();
        }

        return view('themes.xylo.recipes.show', compact('recipe', 'isUnlocked', 'order', 'currency'));
    }

    /**
     * Download Recipe PDF in A5 format.
     */
    public function downloadPdf(string $slug, Request $request)
    {
        $recipe = Recipe::where('slug', $slug)->with('products')->firstOrFail();

        $customer = Auth::guard('sanctum')->user() ?? Auth::guard('customer')->user();
        if (!$customer && $request->bearerToken()) {
            $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            $customer = $pat?->tokenable;
        }
        $orderId = $request->get('order_id');
        $token = $request->get('token');

        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
        }

        $isUnlocked = $this->accessService->hasAccess($recipe, $customer, $orderId ? (int)$orderId : null, $token);

        if (!$isUnlocked) {
            return redirect()->route('recipes.show', $slug)
                ->with('error', 'Purchase the recipe or linked product to unlock this A5 PDF download.');
        }

        $stream = $request->boolean('preview', false);

        return $this->pdfService->generateRecipePdf($recipe, $order, $stream);
    }

    /**
     * Download Order Receipt PDF in A5 format.
     */
    public function downloadReceiptPdf(int $id, Request $request)
    {
        $order = Order::with(['details.product', 'shippingAddress', 'payments'])->findOrFail($id);

        // Security check: owner, guest session, valid token, or admin
        $customer = Auth::guard('sanctum')->user() ?? Auth::guard('customer')->user();
        if (!$customer && $request->bearerToken()) {
            $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            $customer = $pat?->tokenable;
        }
        $token = $request->get('token');
        $isOwner = ($customer && $order->customer_id === $customer->id);
        $isGuestSession = (session('last_order_id') == $order->id);
        $isValidToken = ($token && $this->accessService->verifyOrderToken($order, $token));
        $isAdmin = auth()->check();

        if (!$isOwner && !$isGuestSession && !$isValidToken && !$isAdmin) {
            abort(403, 'Unauthorized to download this order receipt.');
        }

        $stream = $request->boolean('preview', false);

        return $this->pdfService->generateOrderReceiptPdf($order, $stream);
    }
}
