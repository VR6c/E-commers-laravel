<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\Admin\BrandService;
use App\Traits\UpdatesModelStatus;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use UpdatesModelStatus;

    protected BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index()
    {
        return view('admin.brands.index');
    }

    public function getData(Request $request)
    {
        $brands = Brand::query();

        return datatables()->of($brands)
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->addColumn('name', function ($brand) {
                return $brand->name ?? $brand->slug;
            })
            ->addColumn('action', function ($brand) {
                return '';
            })
            ->make(true);
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:5',
        ]);

        $result = $this->brandService->store($request->all());

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'logo_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:5',
        ]);

        $result = $this->brandService->updateBrand($id, $request->all());

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy($id)
    {
        $result = $this->brandService->deleteBrand($id);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting brand.',
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        return $this->performStatusUpdate(Brand::class, $request);
    }
}
