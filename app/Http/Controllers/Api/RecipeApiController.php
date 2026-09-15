<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Services\Store\RecipeAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeApiController extends Controller
{
    protected RecipeAccessService $accessService;

    public function __construct(RecipeAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    // GET /api/recipes
    public function index(Request $request): JsonResponse
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

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'oldest') { $query->oldest(); }
        elseif ($sort === 'title') { $query->orderBy('title'); }
        else { $query->latest(); }

        $perPage   = min((int) $request->input('per_page', 12), 50);
        $paginated = $query->paginate($perPage);

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
    }

    // GET /api/recipes/featured
    public function featured(Request $request): JsonResponse
    {
        $limit   = min((int) $request->input('limit', 6), 20);
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
    }

    // GET /api/recipes/unlocked  (auth required)
    public function unlocked(): JsonResponse
    {
        $customer = Auth::guard('sanctum')->user();
        $recipes  = $this->accessService->getUnlockedRecipesForCustomer($customer);
        $data     = $recipes->map(fn ($r) => $this->formatRecipeSummary($r, true));

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }

    // GET /api/recipes/{slug}
    public function show(string $slug, Request $request): JsonResponse
    {
        $recipe = Recipe::where('slug', $slug)
            ->where('is_active', true)
            ->with('products')
            ->first();

        if (! $recipe) {
            return response()->json(['status' => false, 'message' => 'Recipe not found.'], 404);
        }

        $customer   = Auth::guard('sanctum')->user();
        $orderId    = $request->input('order_id');
        $token      = $request->input('token');
        $isUnlocked = $this->accessService->hasAccess(
            $recipe,
            $customer,
            $orderId ? (int) $orderId : null,
            $token
        );

        return response()->json([
            'status' => true,
            'data'   => $this->formatRecipeDetail($recipe, $isUnlocked),
        ])->header('Cache-Control', 'private, no-cache');
    }

    // ── Private helpers ──────────────────────────────────────────────

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

    private function formatRecipeDetail(Recipe $recipe, bool $isUnlocked): array
    {
        $base = $this->formatRecipeSummary($recipe, $isUnlocked);

        $base['products'] = $recipe->products->map(fn ($p) => [
            'id'   => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
        ])->values()->all();

        if ($isUnlocked) {
            $base['content']          = $recipe->content;
            $base['ingredients']      = $recipe->ingredients ?? [];
            $base['instructions']     = $recipe->instructions ?? [];
            $base['nutritional_info'] = $recipe->nutritional_info ?? [];
        } else {
            $base['content']          = null;
            $base['ingredients']      = [];
            $base['instructions']     = [];
            $base['nutritional_info'] = [];
            $base['locked_message']   = 'Purchase a linked product or this recipe to unlock the full content.';
        }

        return $base;
    }
}
