<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use App\Traits\UpdatesModelStatus;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use UpdatesModelStatus;

    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        return view('admin.categories.index');
    }

    public function getCategories(Request $request)
    {
        if ($request->ajax()) {
            return $this->categoryService->getCategoriesForDataTable($request);
        }
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:5',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $translations = ['en' => [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'image' => $request->file('image'),
        ]];

        $result = $this->categoryService->store($translations);

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $this->categoryService->update($request, $id);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $result = $this->categoryService->destroy($id);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category.',
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        return $this->performStatusUpdate(Category::class, $request);
    }
}
