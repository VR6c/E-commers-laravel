<?php

namespace App\Services\Vendor;

use App\Models\Product;
use App\Repositories\Shared\Product\ProductRepository;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductsForDataTable($request)
    {
        $vendorId = auth()->guard('vendor')->id();

        $products = Product::with(['variants' => fn ($q) => $q->whereRaw('is_primary is true')])
            ->where('vendor_id', $vendorId);

        return DataTables::of($products)
            ->filterColumn('name', fn ($query, $keyword) =>
                $query->where('products.name', 'like', "%{$keyword}%")
            )
            ->addColumn('name', fn ($p) => $p->name ?? 'No name')
            ->addColumn('price', fn ($p) => ($pv = $p->variants->first())
                ? '$' . number_format($pv->price, 2) : 'No price')
            ->addColumn('status', fn ($p) => $p->status)
            ->addColumn('action', fn ($p) => '')
            ->rawColumns(['action'])
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
