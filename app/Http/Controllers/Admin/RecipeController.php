<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Recipe;
use App\Traits\GeneratesUniqueSlug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class RecipeController extends Controller
{
    use GeneratesUniqueSlug;

    public function index()
    {
        return view('admin.recipes.index');
    }

    public function getData(Request $request)
    {
        $recipes = Recipe::query()
            ->select(['id', 'title', 'slug', 'image_url', 'prep_time', 'cook_time', 'difficulty', 'cuisine', 'is_active', 'created_at'])
            ->withCount('products');

        return DataTables::of($recipes)
            ->filterColumn('title', fn ($q, $kw) => $q->where('title', 'like', "%{$kw}%"))
            ->addColumn('image', function ($recipe) {
                $url = $recipe->image_src;
                return '<img src="' . htmlspecialchars($url) . '" alt="recipe" class="rounded" style="width: 44px; height: 44px; object-fit: cover; border: 1px solid #e2e8f0;" onerror="this.src=\'https://placehold.co/44x44/eee/999?text=Recipe\';">';
            })
            ->addColumn('time', function ($recipe) {
                $total = ($recipe->prep_time ?? 0) + ($recipe->cook_time ?? 0);
                return '<span>' . ($total ? $total . ' mins' : '—') . '</span>';
            })
            ->addColumn('difficulty', function ($recipe) {
                $color = match(strtolower($recipe->difficulty ?? 'medium')) {
                    'easy'   => 'success',
                    'medium' => 'warning',
                    'hard'   => 'danger',
                    default  => 'secondary',
                };
                return '<span class="badge bg-' . $color . '-subtle text-' . $color . ' text-uppercase" style="font-size: 0.72rem;">' . htmlspecialchars($recipe->difficulty ?? 'Medium') . '</span>';
            })
            ->addColumn('products_count', function ($recipe) {
                return '<span class="badge bg-secondary-subtle text-secondary">' . $recipe->products_count . ' linked products</span>';
            })
            ->addColumn('status', function ($recipe) {
                $checked = $recipe->is_active ? 'checked' : '';
                return '
                    <div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" role="switch"
                               data-id="' . $recipe->id . '" ' . $checked . '>
                    </div>';
            })
            ->addColumn('action', function ($recipe) {
                $viewUrl = route('recipes.show', $recipe->slug);
                $editUrl = route('admin.recipes.edit', $recipe->id);
                $deleteUrl = route('admin.recipes.destroy', $recipe->id);

                return '
                    <div class="d-flex align-items-center gap-1">
                        <a href="' . $viewUrl . '" target="_blank" class="btn btn-sm btn-outline-info" title="Preview Recipe on Store">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary" title="Edit Recipe">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="' . $deleteUrl . '" data-id="' . $recipe->id . '" title="Delete Recipe">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['image', 'time', 'difficulty', 'products_count', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $products = Product::select('id', 'name')->orderBy('name')->get();
        return view('admin.recipes.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'summary'     => 'nullable|string|max:1000',
            'content'     => 'nullable|string',
            'prep_time'   => 'nullable|integer|min:0',
            'cook_time'   => 'nullable|integer|min:0',
            'servings'    => 'nullable|integer|min:1',
            'difficulty'  => 'required|in:easy,medium,hard',
            'cuisine'     => 'nullable|string|max:100',
            'calories'    => 'nullable|integer|min:0',
            'image'       => 'nullable|image|max:4096',
            'image_url'   => 'nullable|string|url|max:500',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $slug = $this->generateUniqueSlug($request->title, 'recipes');

                $imageUrl = $request->input('image_url');
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('recipes', 'public');
                    $imageUrl = $path;
                }

                // Process structured ingredients
                $ingredients = [];
                if ($request->filled('ingredients_data')) {
                    $ingredients = json_decode($request->ingredients_data, true) ?: [];
                } elseif (is_array($request->input('ingredients'))) {
                    $ingredients = array_values(array_filter($request->input('ingredients'), fn($i) => !empty($i['item'])));
                }

                // Process structured instructions
                $instructions = [];
                if ($request->filled('instructions_data')) {
                    $instructions = json_decode($request->instructions_data, true) ?: [];
                } elseif (is_array($request->input('instructions'))) {
                    $instructions = array_values(array_filter($request->input('instructions'), fn($step) => !empty($step['text'] ?? $step['description'])));
                }

                $recipe = Recipe::create([
                    'title'        => $request->title,
                    'slug'         => $slug,
                    'summary'      => $request->summary,
                    'content'      => $request->content,
                    'image_url'    => $imageUrl,
                    'prep_time'    => $request->prep_time,
                    'cook_time'    => $request->cook_time,
                    'servings'     => $request->servings ?? 2,
                    'difficulty'   => $request->difficulty,
                    'cuisine'      => $request->cuisine,
                    'calories'     => $request->calories,
                    'ingredients'  => $ingredients,
                    'instructions' => $instructions,
                    'is_active'    => $request->boolean('is_active', true),
                    'is_featured'  => $request->boolean('is_featured', false),
                ]);

                if ($request->filled('product_ids')) {
                    $recipe->products()->sync($request->product_ids);
                }
            });

            return redirect()->route('admin.recipes.index')->with('success', 'Recipe created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating recipe: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating recipe: ' . $e->getMessage());
        }
    }

    public function edit(Recipe $recipe)
    {
        $recipe->load('products');
        $products = Product::select('id', 'name')->orderBy('name')->get();
        return view('admin.recipes.edit', compact('recipe', 'products'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'summary'     => 'nullable|string|max:1000',
            'content'     => 'nullable|string',
            'prep_time'   => 'nullable|integer|min:0',
            'cook_time'   => 'nullable|integer|min:0',
            'servings'    => 'nullable|integer|min:1',
            'difficulty'  => 'required|in:easy,medium,hard',
            'cuisine'     => 'nullable|string|max:100',
            'calories'    => 'nullable|integer|min:0',
            'image'       => 'nullable|image|max:4096',
            'image_url'   => 'nullable|string|url|max:500',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        try {
            DB::transaction(function () use ($request, $recipe) {
                $imageUrl = $request->input('image_url') ?: $recipe->image_url;
                if ($request->hasFile('image')) {
                    if ($recipe->image_url && !str_starts_with($recipe->image_url, 'http')) {
                        Storage::disk('public')->delete($recipe->image_url);
                    }
                    $imageUrl = $request->file('image')->store('recipes', 'public');
                }

                // Process structured ingredients
                $ingredients = $recipe->ingredients;
                if ($request->filled('ingredients_data')) {
                    $ingredients = json_decode($request->ingredients_data, true) ?: [];
                } elseif (is_array($request->input('ingredients'))) {
                    $ingredients = array_values(array_filter($request->input('ingredients'), fn($i) => !empty($i['item'])));
                }

                // Process structured instructions
                $instructions = $recipe->instructions;
                if ($request->filled('instructions_data')) {
                    $instructions = json_decode($request->instructions_data, true) ?: [];
                } elseif (is_array($request->input('instructions'))) {
                    $instructions = array_values(array_filter($request->input('instructions'), fn($step) => !empty($step['text'] ?? $step['description'])));
                }

                $recipe->update([
                    'title'        => $request->title,
                    'summary'      => $request->summary,
                    'content'      => $request->content,
                    'image_url'    => $imageUrl,
                    'prep_time'    => $request->prep_time,
                    'cook_time'    => $request->cook_time,
                    'servings'     => $request->servings ?? 2,
                    'difficulty'   => $request->difficulty,
                    'cuisine'      => $request->cuisine,
                    'calories'     => $request->calories,
                    'ingredients'  => $ingredients,
                    'instructions' => $instructions,
                    'is_active'    => $request->boolean('is_active', true),
                    'is_featured'  => $request->boolean('is_featured', false),
                ]);

                $recipe->products()->sync($request->product_ids ?? []);
            });

            return redirect()->route('admin.recipes.index')->with('success', 'Recipe updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating recipe: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error updating recipe: ' . $e->getMessage());
        }
    }

    public function destroy(Recipe $recipe)
    {
        try {
            $recipe->products()->detach();
            $recipe->delete();

            return response()->json(['status' => 'success', 'message' => 'Recipe deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Error deleting recipe: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to delete recipe.'], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:recipes,id',
        ]);

        $recipe = Recipe::findOrFail($request->id);
        $recipe->is_active = !$recipe->is_active;
        $recipe->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Recipe status updated successfully!',
            'active'  => $recipe->is_active,
        ]);
    }
}
