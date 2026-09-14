<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductVariantController extends Controller
{
    public function index()
    {
        $productVariants = ProductVariant::with('product')
            ->latest()
            ->paginate(15);

        return view('admin.product_variants.index', compact('productVariants'));
    }

    public function getData(Request $request)
    {
        $productVariants = ProductVariant::with('product')
            ->select('product_variants.*');

        return DataTables::of($productVariants)
            ->addColumn('id', fn ($pv) => $pv->id)
            ->addColumn('product', fn ($pv) => '<span class="fw-semibold" style="color: var(--vp-text);">' . e($pv->product->name ?? 'Unknown Product') . '</span>')
            ->addColumn('variant_name', function ($pv) {
                return '<span class="vp-status-badge inactive" style="background: var(--vp-primary-bg); color: var(--vp-primary); font-weight: 600;">' . e($pv->name ?? '—') . '</span>';
            })
            ->addColumn('price', fn ($pv) => '<span style="font-weight: 700; color: var(--vp-primary); font-size: .875rem;">$' . number_format((float) $pv->price, 2) . '</span>')
            ->addColumn('stock', function ($pv) {
                if ($pv->stock <= 0) {
                    return '<span class="vp-status-badge cancelled">' . (int)$pv->stock . ' — Out</span>';
                } elseif ($pv->stock <= 5) {
                    return '<span class="vp-status-badge pending">' . (int)$pv->stock . ' — Low</span>';
                }
                return '<span class="vp-status-badge active">' . (int)$pv->stock . '</span>';
            })
            ->addColumn('sku', fn ($pv) => '<code style="font-size: .78rem; background: var(--vp-surface-muted); padding: 3px 8px; border-radius: var(--vp-r-xs); border: 1px solid var(--vp-border-sub); color: var(--vp-text-2);">' . e($pv->SKU ?? '—') . '</code>')
            ->addColumn('action', function ($pv) {
                return '<div class="dt-actions">
                    <a href="'.route('admin.product_variants.edit', $pv->id).'" class="btn-action btn-action-edit" title="Edit variant" aria-label="Edit variant">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <button type="button" class="btn-action btn-action-delete" onclick="deleteVariant('.$pv->id.')" title="Delete variant" aria-label="Delete variant">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['product', 'variant_name', 'price', 'stock', 'sku', 'action'])
            ->make(true);
    }

    public function create()
    {
        $products = Product::where('status', 1)->get();

        return view('admin.product_variants.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric',
            'stock'      => 'required|integer',
        ]);

        $data = $request->only(['product_id', 'name', 'price', 'stock', 'SKU', 'barcode', 'discount_price', 'weight', 'dimensions', 'is_primary']);
        $data['variant_slug'] = Str::slug($request->name);

        ProductVariant::create($data);

        return redirect()->route('admin.product_variants.index')->with('success', 'Product Variant created successfully.');
    }

    public function edit($id)
    {
        $productVariant = ProductVariant::findOrFail($id);
        $products = Product::where('status', 1)->get();

        return view('admin.product_variants.edit', compact('productVariant', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $productVariant = ProductVariant::findOrFail($id);
        $data = $request->only(['product_id', 'name', 'price', 'stock', 'SKU', 'barcode', 'discount_price', 'weight', 'dimensions', 'is_primary']);
        $data['variant_slug'] = Str::slug($request->name);
        $productVariant->update($data);

        return redirect()->route('admin.product_variants.index')->with('success', 'Product Variant updated successfully.');
    }

    public function destroy($id)
    {
        try {
            ProductVariant::findOrFail($id)->delete();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Product Variant deleted successfully.']);
            }

            return redirect()->route('admin.product_variants.index')->with('success', 'Product Variant deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while deleting the product variant.']);
            }

            return redirect()->route('admin.product_variants.index')->with('error', 'An error occurred while deleting the product variant.');
        }
    }
}
