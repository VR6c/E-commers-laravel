<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\CategoryService;
use App\Services\Admin\ProductService;
use App\Traits\GeneratesUniqueSlug;
use App\Traits\SyncsProductVariants;
use App\Traits\UpdatesModelStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use GeneratesUniqueSlug, SyncsProductVariants, UpdatesModelStatus;

    protected CategoryService $categoryService;
    protected ProductService $productService;

    public function __construct(CategoryService $categoryService, ProductService $productService)
    {
        $this->categoryService = $categoryService;
        $this->productService  = $productService;
    }

    public function index()
    {
        return view('admin.products.index');
    }

    public function getProducts(Request $request)
    {
        try {
            return $this->productService->getProductsForDataTable($request);
        } catch (\Exception $e) {
            Log::error('Error fetching product data: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching product data.'], 500);
        }
    }

    public function create()
    {
        $vendors  = Vendor::all();
        $categories = Category::all();
        $brands   = Brand::all();
        $attributes = Attribute::with('values')->get();
        $sizes    = Attribute::where('name', 'Size')->first()?->values ?? collect();
        $colors   = Attribute::where('name', 'Color')->first()?->values ?? collect();
        $attributeSizeMap = [
            'small'  => AttributeValue::where('attribute_id', $sizes->firstWhere('name', 'Small')->id ?? 0)->pluck('id')->first(),
            'medium' => AttributeValue::where('attribute_id', $sizes->firstWhere('name', 'Medium')->id ?? 0)->pluck('id')->first(),
            'large'  => AttributeValue::where('attribute_id', $sizes->firstWhere('name', 'Large')->id ?? 0)->pluck('id')->first(),
        ];

        return view('admin.products.create', compact('categories', 'brands', 'attributes', 'sizes', 'colors', 'attributeSizeMap', 'vendors'));
    }

    public function store(ProductRequest $request)
    {
        // Resolve the shop for the selected vendor — required for data integrity.
        $shop = Shop::where('vendor_id', $request->vendor_id)->first();

        if (! $shop) {
            return back()->withErrors(['vendor_id' => 'No shop found for the selected vendor.'])->withInput();
        }

        DB::transaction(function () use ($request, $shop) {
            $slug    = $this->generateUniqueSlug($request->input('name'));
            $product = Product::create([
                'shop_id'           => $shop->id,
                'vendor_id'         => $request->vendor_id,
                'slug'              => $slug,
                'name'              => $request->input('name'),
                'description'       => $request->input('description'),
                'short_description' => $request->input('short_description'),
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

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $product    = Product::with(['variants.attributeValues', 'images'])->findOrFail($id);
        $vendors    = Vendor::all();
        $categories = Category::all();
        $brands     = Brand::all();
        $attributes = Attribute::with('values')->get();
        $sizes      = Attribute::where('name', 'Size')->first()?->values ?? collect();
        $colors     = Attribute::where('name', 'Color')->first()?->values ?? collect();

        foreach ($product->variants as $variant) {
            $size  = $variant->attributeValues->firstWhere('attribute_id', $sizes->first()?->attribute_id);
            $color = $variant->attributeValues->firstWhere('attribute_id', $colors->first()?->attribute_id);
            $variant->size_id  = $size?->id;
            $variant->color_id = $color?->id;
        }

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'attributes', 'sizes', 'colors', 'vendors'));
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        DB::transaction(function () use ($request, $product) {
            $en = $request->only(['name', 'description', 'short_description']);
            $product->update([
                'category_id'       => $request->category_id,
                'brand_id'          => $request->brand_id,
                'vendor_id'         => $request->vendor_id,
                'name'              => $en['name'] ?? $product->name,
                'description'       => $en['description'] ?? $product->description,
                'short_description' => $en['short_description'] ?? $product->short_description,
            ]);

            // If the vendor changed, update the shop_id accordingly.
            $shop = Shop::where('vendor_id', $request->vendor_id)->first();
            if ($shop) {
                $product->update(['shop_id' => $shop->id]);
            }

            $newAttrValueIds = collect($request->variants)
                ->flatMap(fn ($v) => array_filter([$v['size_id'] ?? null, $v['color_id'] ?? null]))
                ->unique()->values()->all();

            ProductAttributeValue::where('product_id', $product->id)
                ->whereNotIn('attribute_value_id', $newAttrValueIds)
                ->delete();

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

            $this->syncVariants($request->variants, $product);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $result = $this->productService->destroy($id);

            if ($result) {
                return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
            }

            return response()->json(['success' => false, 'message' => 'Failed to delete product!']);
        } catch (\Exception $e) {
            Log::error("Error deleting product with ID {$id}: " . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the product.']);
        }
    }

    public function updateStatus(Request $request)
    {
        return $this->performStatusUpdate(Product::class, $request);
    }

}
