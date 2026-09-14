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

        $products = Product::with('variants')
            ->where('vendor_id', $vendorId);

        return DataTables::of($products)
            ->filterColumn('name', fn ($query, $keyword) =>
                $query->where('products.name', 'like', "%{$keyword}%")
            )
            ->addColumn('name', fn ($p) => $p->name ?? 'No name')
            ->addColumn('price', function ($p) {
                $pv = $p->variants->firstWhere('is_primary', true) ?? $p->variants->first();

                return $pv ? '$' . number_format((float) $pv->price, 2) : '—';
            })
            ->addColumn('status', fn ($p) => $p->status)
            ->addColumn('action', function ($p) {
                return '
                    <div class="dt-actions">
                        <a href="' . route('vendor.products.edit', $p->id) . '"
                           class="btn-action btn-action-edit" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <button type="button"
                                class="btn-action btn-action-delete"
                                onclick="deleteProduct(' . $p->id . ')" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>';
            })
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
