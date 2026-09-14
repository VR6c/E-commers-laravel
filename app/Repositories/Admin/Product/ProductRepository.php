<?php

namespace App\Repositories\Admin\Product;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Shared\ImageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductRepository implements ProductRepositoryInterface
{
    protected $imageService;

    // Inject ImageService into the repository constructor
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function all()
    {
        return Product::all();
    }

    public function find($id)
    {
        return Product::findOrFail($id);
    }

    public function store($data)
    {
        $sku = $data['SKU'];
        $skuCounter = 1;

        while (Product::where('SKU', $sku)->exists()) {
            $sku = $data['SKU'].'-'.$skuCounter;
            $skuCounter++;
        }

        $defaultCurrencyCode = getWebConfig('default_currency', 'USD');

        $product = Product::create([
            'vendor_id' => 1,
            'category_id' => $data['category_id'],
            'price' => currency_to_usd($data['price'], $defaultCurrencyCode),
            'stock' => $data['stock'],
            'status' => $data['status'] ?? true,
            'slug' => $data['slug'],
            'currency' => $data['currency'],
            'SKU' => $sku,
            'weight' => $data['weight'],
            'dimensions' => $data['dimensions'],
            'product_type' => $data['product_type'],
        ]);

        if (isset($data['product_image_url']) && $data['product_image_url'] instanceof \Illuminate\Http\UploadedFile) {
            $imagePath = $this->imageService->uploadImage($data['product_image_url'], 'products');

            $productImage = new ProductImage([
                'name' => basename($imagePath),
                'image_url' => $imagePath,
                'product_id' => $product->id,
                'type' => $data['image_type'] ?? 'thumb',
            ]);

            $productImage->save();
        }

        return $product;
    }

    public function update($id, array $data)
    {
        // Dead code — controllers handle product updates directly via Eloquent transactions.
        // Retained to satisfy the interface contract.
        throw new \LogicException('ProductRepository::update() is not used. Update products via the controller transaction.');
    }

    public function destroy($id)
    {
        $product = $this->find($id);

        $productImages = $product->images;

        foreach ($productImages as $productImage) {
            if ($productImage->image_url) {
                $this->imageService->deleteImage($productImage->image_url);
            }

            $productImage->delete();
        }

        return $product->delete();
    }
}
