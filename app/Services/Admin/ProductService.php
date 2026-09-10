<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Repositories\Shared\Product\ProductRepository;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductsForDataTable($request)
    {
        $products = Product::with([
            'images',
            'category',
            'primaryVariant' => fn ($q) => $q->whereRaw('is_primary is true'),
        ]);

        return DataTables::of($products)
            ->filterColumn('name', fn ($q, $kw) => $q->where('name', 'like', "%{$kw}%"))
            ->addColumn('image', function ($product) {
                $image = $product->images->first();
                if ($image) {
                    $url = asset('storage/' . $image->image_url);
                    return '<img src="' . $url . '" alt="product" class="dt-product-thumb">';
                }
                return '<div class="dt-product-thumb dt-product-thumb--placeholder"><i class="bi bi-image"></i></div>';
            })
            ->addColumn('name', fn ($p) => $p->name ?? 'No name')
            ->addColumn('category', function ($product) {
                $name = $product->category?->name ?? '—';
                return '<span class="dt-category-badge">' . $name . '</span>';
            })
            ->addColumn('price', function ($product) {
                $pv = $product->variants->firstWhere('is_primary', true);
                return $pv ? '$' . number_format($pv->price, 2) : '<span class="text-muted">—</span>';
            })
            ->addColumn('stock', function ($product) {
                $pv = $product->variants->firstWhere('is_primary', true);
                if (!$pv) return '<span class="text-muted">—</span>';
                $stock = (int) $pv->stock;
                if ($stock === 0) return '<span class="dt-stock-badge dt-stock-badge--out">Out of stock</span>';
                if ($stock <= 10) return '<span class="dt-stock-badge dt-stock-badge--low">' . $stock . '</span>';
                return '<span class="dt-stock-badge dt-stock-badge--in">' . $stock . '</span>';
            })
            ->addColumn('status', fn ($p) => $p->status)
            ->addColumn('action', fn () => '')
            ->rawColumns(['image', 'category', 'price', 'stock', 'action'])
            ->make(true);
    }

    public function destroy($id)
    {
        try {
            return $this->productRepository->destroy($id);
        } catch (\Exception $e) {
            Log::error("Error deleting product {$id}: " . $e->getMessage());
            return false;
        }
    }
}
