<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Repositories\Admin\Category\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategoriesForDataTable($request)
    {
        $categories = Category::query();

        return DataTables::of($categories)
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->addColumn('name', function ($category) {
                return $category->name ?? '—';
            })
            ->addColumn('description', function ($category) {
                return $category->description ?? '—';
            })
            ->addColumn('action', function ($category) {
                return ''; // JS handles rendering
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created category.
     */
    public function store(array $translations)
    {
        // $translations is keyed by lang code; 'en' holds name/description/image
        $validator = Validator::make($translations['en'] ?? [], [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
        ], []);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return $this->categoryRepository->storeWithTranslations($translations);
    }

    public function update($request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
        ]);

        // Wrap into translations array for the repository
        $translations = ['en' => array_merge(
            $request->only(['name', 'description']),
            $request->hasFile('image') ? ['image' => $request->file('image')] : []
        )];

        return $this->categoryRepository->updateWithTranslations($category, $translations);
    }

    /**
     * Delete an existing category.
     */
    public function destroy($id)
    {
        // Call the repository to delete the category
        return $this->categoryRepository->destroy($id);
    }

    /**
     * Find a category by its ID.
     */
    public function find($id)
    {
        // Call the repository to find the category by ID
        return $this->categoryRepository->find($id);
    }
}
