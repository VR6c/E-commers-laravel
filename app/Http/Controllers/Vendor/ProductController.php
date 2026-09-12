<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\ProductRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Shop;
use App\Services\Admin\CategoryService;
use App\Services\Vendor\ProductService;
use App\Traits\GeneratesUniqueSlug;
use App\Traits\SyncsProductVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use GeneratesUniqueSlug, SyncsProductVariants;

    protected CategoryService $categoryService;
    protected ProductService $productService;

    public function __construct(CategoryService $categoryService, ProductService $productService)
    {
        $this->middleware('auth:vendor');
        $this->categoryService = $categoryService;
        $this->productService  = $productService;
    }

    public function index()
    {
        return view('vendor.products.index');
    }

    public function getProducts(Request $request)
    {
        try {
            $request->merge(['vendor_id' => auth()->guard('vendor')->id()]);

            return $this->productService->getProductsForDataTable($request);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor product data: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching products.'], 500);
        }
    }

    public function create()
    {
        $categories = Category::all();
        $brands     = Brand::all();
        $attributes = Attribute::with('values')->get();
        $sizes      = Attribute::where('name', 'Size')->first()?->values ?? collect();
        $colors     = Attribute::where('name', 'Color')->first()?->values ?? collect();
        $attributeSizeMap = [
            'small'  => AttributeValue::where('attribute_id', $sizes->firstWhere('name', 'Small')->id ?? 0)->pluck('id')->first(),
            'medium' => AttributeValue::where('attribute_id', $sizes->firstWhere('name', 'Medium')->id ?? 0)->pluck('id')->first(),
            'large'  => AttributeValue::where('attribute_id', $sizes->firstWhere('name', 'Large')->id ?? 0)->pluck('id')->first(),
        ];

        return view('vendor.products.create', compact('categories', 'brands', 'attributes', 'sizes', 'colors', 'attributeSizeMap'));
    }

    public function store(ProductRequest $request)
    {
        $vendorId = Auth::guard('vendor')->id();
        $vendor   = Auth::guard('vendor')->user();

        // Resolve or auto-create the vendor's shop.
        $shop = Shop::firstOrCreate(
            ['vendor_id' => $vendorId],
            [
                'name'        => $vendor->name . "'s Shop",
                'slug'        => \Illuminate\Support\Str::slug($vendor->name . '-shop-' . $vendorId),
                'description' => null,
                'status'      => 'active',
            ]
        );

        DB::transaction(function () use ($request, $vendorId, $shop) {
            $slug = $this->generateUniqueSlug($request->input('name'));
            $en   = $request->only(['name', 'description', 'short_description']);

            $product = Product::create([
                'shop_id'           => $shop->id,
                'vendor_id'         => $vendorId,
                'slug'              => $slug,
                'name'              => $en['name'],
                'description'       => $en['description'] ?? null,
                'short_description' => $en['short_description'] ?? null,
                'category_id'       => $request->category_id,
                'brand_id'          => $request->brand_id,
                'product_type'      => 'variable',
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = \App\Services\ImageUploadService::upload($image, 'products');
                    $product->images()->create(['name' => $image->getClientOriginalName(), 'image_url' => $path, 'type' => 'thumb']);
                }
            }

            $this->syncVariants($request->variants, $product);
        });

        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $vendorId = auth('vendor')->id();

        $product    = Product::with(['images', 'variants.attributeValues'])
            ->where('vendor_id', $vendorId)
            ->where('id', $id)
            ->firstOrFail();

        $categories = Category::all();
        $brands     = Brand::all();
        $attributes = Attribute::with('values')->get();
        $sizes      = Attribute::where('name', 'Size')->first()?->values ?? collect();
        $colors     = Attribute::where('name', 'Color')->first()?->values ?? collect();

        return view('vendor.products.edit', compact('product', 'categories', 'brands', 'attributes', 'sizes', 'colors'));
    }

    public function update(ProductRequest $request, $id)
    {
        $vendorId = Auth::guard('vendor')->id();
        $product  = Product::with(['images'])->where('vendor_id', $vendorId)->findOrFail($id);

        DB::transaction(function () use ($request, $product) {
            $en = $request->only(['name', 'description', 'short_description']);
            $product->update([
                'category_id'       => $request->category_id,
                'brand_id'          => $request->brand_id,
                'name'              => $en['name'] ?? $product->name,
                'description'       => $en['description'] ?? $product->description,
                'short_description' => $en['short_description'] ?? $product->short_description,
            ]);

            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $imageId) {
                    $image = $product->images()->find($imageId);
                    if ($image) {
                        if (!\Illuminate\Support\Str::startsWith($image->image_url, ['http://', 'https://'])) {
                            Storage::disk('public')->delete($image->image_url);
                        }
                        $image->delete();
                    }
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = \App\Services\ImageUploadService::upload($image, 'products');
                    $product->images()->create(['name' => $image->getClientOriginalName(), 'image_url' => $path, 'type' => 'thumb']);
                }
            }

            $product->variants()->delete();
            DB::table('product_variant_attribute_values')->where('product_id', $product->id)->delete();
            ProductAttributeValue::where('product_id', $product->id)->delete();

            $this->syncVariants($request->variants, $product);
        });

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $vendorId = Auth::guard('vendor')->id();
            $product  = Product::where('vendor_id', $vendorId)->findOrFail($id);

            $product->images()->delete();
            $product->variants()->delete();
            DB::table('product_variant_attribute_values')->where('product_id', $product->id)->delete();
            ProductAttributeValue::where('product_id', $product->id)->delete();
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Vendor product delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the product.',
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:products,id',
            'status' => 'required|boolean',
        ]);

        $product = Product::where('vendor_id', Auth::guard('vendor')->id())->find($request->id);

        if ($product) {
            $product->status = $request->status;
            $product->save();

            return response()->json(['success' => true, 'message' => 'Product status updated.']);
        }

        return response()->json(['success' => false, 'message' => 'Product not found or access denied.']);
    }

}
