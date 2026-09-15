<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Services\Store\RecipeAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecipeApiController extends Controller
{
    protected RecipeAccessService $accessService;

    public function __construct(RecipeAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    // ── GET /api/recipes ──────────────────────────────────────────────
    // Paginated catalog with optional search / filter / sort.
    // Lock-awareness: includes `is_unlocked` flag per recipe when auth token present.
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Recipe::active()->with('products');

            // Keyword search across title, summary, and cuisine
            if ($request->filled('q')) {
                $keyword = trim($request->q);
                $query->where(function ($q) use ($keyword) {
                    $q->where('title',   'like', "%{$keyword}%")
                      ->orWhere('summary',  'like', "%{$keyword}%")
                      ->orWhere('cuisine',  'like', "%{$keyword}%");
                });
            }

            // Exact-match filters
            if ($request->filled('cuisine')) {
                $query->where('cuisine', $request->cuisine);
            }
            if ($request->filled('difficulty')) {
                $query->whereIn('difficulty', ['easy', 'medium', 'hard'])
                      ->where('difficulty', $request->difficulty);
            }
            if ($request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            // Sorting
            $sort = $request->input('sort', 'latest');
            match ($sort) {
                'oldest' => $query->oldest(),
                'title'  => $query->orderBy('title'),
                default  => $query->latest(),
            };

            // Pagination — clamped between 1 and 50, default 12
            $perPage   = min(max((int) $request->input('per_page', 12), 1), 50);
            $paginated = $query->paginate($perPage);

            // Resolve unlocked recipe IDs for the authenticated customer
            $customer    = Auth::guard('sanctum')->user();
            $unlockedIds = [];
            if ($customer) {
                $unlockedIds = $this->accessService
                    ->getUnlockedRecipesForCustomer($customer)
                    ->pluck('id')
                    ->toArray();
            }

            $data = $paginated->getCollection()->map(
                fn ($r) => $this->formatRecipeSummary($r, in_array($r->id, $unlockedIds))
            );

            // Distinct cuisine list for filter chips on the client
            $cuisines = Recipe::active()->whereNotNull('cuisine')->distinct()->pluck('cuisine');

            return response()->json([
                'status'   => true,
                'data'     => $data,
                'cuisines' => $cuisines,
                'meta'     => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                ],
            ])->header('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');

        } catch (\Throwable $e) {
            Log::error('RecipeApiController@index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to load recipes. Please try again later.',
            ], 500);
        }
    }

    // ── GET /api/recipes/featured ─────────────────────────────────────
    // Up to 6 (configurable via ?limit=N, max 20) featured active recipes.
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit   = min(max((int) $request->input('limit', 6), 1), 20);
            $recipes = Recipe::active()->featured()->with('products')->latest()->limit($limit)->get();

            $customer    = Auth::guard('sanctum')->user();
            $unlockedIds = [];
            if ($customer) {
                $unlockedIds = $this->accessService
                    ->getUnlockedRecipesForCustomer($customer)
                    ->pluck('id')
                    ->toArray();
            }

            $data = $recipes->map(
                fn ($r) => $this->formatRecipeSummary($r, in_array($r->id, $unlockedIds))
            );

            return response()->json([
                'status' => true,
                'data'   => $data,
            ])->header('Cache-Control', 'public, max-age=120, s-maxage=600, stale-while-revalidate=1200');

        } catch (\Throwable $e) {
            Log::error('RecipeApiController@featured failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to load featured recipes. Please try again later.',
            ], 500);
        }
    }

    // ── GET /api/recipes/{slug} ───────────────────────────────────────
    // Full recipe detail. Gated fields (ingredients, instructions, nutritional_info, content)
    // are only populated when the recipe is unlocked for the caller.
    // Supports ?order_id=N&token=X for guest post-purchase unlock.
    public function show(string $slug, Request $request): JsonResponse
    {
        try {
            $recipe = Recipe::where('slug', $slug)
                ->where('is_active', true)
                ->with('products')
                ->first();

            if (! $recipe) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Recipe not found.',
                ], 404);
            }

            $customer   = Auth::guard('sanctum')->user();
            $orderId    = $request->input('order_id');
            $token      = $request->input('token');

            $isUnlocked = $this->accessService->hasAccess(
                $recipe,
                $customer,
                $orderId !== null ? (int) $orderId : null,
                $token
            );

            return response()->json([
                'status' => true,
                'data'   => $this->formatRecipeDetail($recipe, $isUnlocked),
            ])->header('Cache-Control', 'private, no-cache');

        } catch (\Throwable $e) {
            Log::error('RecipeApiController@show failed', [
                'slug'  => $slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to load recipe. Please try again later.',
            ], 500);
        }
    }

    // ── GET /api/recipes/unlocked  (auth:sanctum required) ───────────
    // Returns every active recipe that is personally unlocked for the authenticated customer
    // (i.e. they have at least one completed/processing order containing a linked product).
    public function unlocked(): JsonResponse
    {
        try {
            $customer = Auth::guard('sanctum')->user();

            // Belt-and-suspenders guard: middleware should always enforce auth, but just in case
            if (! $customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $recipes = $this->accessService->getUnlockedRecipesForCustomer($customer);
            $data    = $recipes->map(fn ($r) => $this->formatRecipeSummary($r, true));

            return response()->json([
                'status' => true,
                'data'   => $data,
            ]);

        } catch (\Throwable $e) {
            Log::error('RecipeApiController@unlocked failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to load unlocked recipes. Please try again later.',
            ], 500);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────

    /**
     * Compact summary shape — used in list, featured, and unlocked endpoints.
     */
    private function formatRecipeSummary(Recipe $recipe, bool $isUnlocked): array
    {
        return [
            'id'          => $recipe->id,
            'slug'        => $recipe->slug,
            'title'       => $recipe->title,
            'summary'     => $recipe->summary,
            'image'       => $recipe->image_src,
            'cuisine'     => $recipe->cuisine,
            'difficulty'  => $recipe->difficulty,
            'prep_time'   => $recipe->prep_time,
            'cook_time'   => $recipe->cook_time,
            'total_time'  => $recipe->total_time,
            'servings'    => $recipe->servings,
            'calories'    => $recipe->calories,
            'price'       => (float) $recipe->price,
            'is_free'     => (float) $recipe->price === 0.0 && $recipe->products->isEmpty(),
            'is_featured' => $recipe->is_featured,
            'is_unlocked' => $isUnlocked,
            'created_at'  => $recipe->created_at?->toISOString(),
        ];
    }

    /**
     * Full detail shape — used in the single-recipe show endpoint.
     * Gated fields are only populated when $isUnlocked is true.
     */
    private function formatRecipeDetail(Recipe $recipe, bool $isUnlocked): array
    {
        $base = $this->formatRecipeSummary($recipe, $isUnlocked);

        // Linked products (always visible — helps the client show purchase CTAs)
        $base['products'] = $recipe->products->map(fn ($p) => [
            'id'   => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
        ])->values()->all();

        if ($isUnlocked) {
            $base['content']          = $recipe->content;
            $base['ingredients']      = $recipe->ingredients      ?? [];
            $base['instructions']     = $recipe->instructions     ?? [];
            $base['nutritional_info'] = $recipe->nutritional_info ?? [];
        } else {
            $base['content']          = null;
            $base['ingredients']      = [];
            $base['instructions']     = [];
            $base['nutritional_info'] = [];
            $base['locked_message']   = 'Purchase a linked product to unlock the full recipe.';
        }

        return $base;
    }
}
