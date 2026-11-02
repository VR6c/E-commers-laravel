<?php

namespace App\Repositories\Shared\Product;

use App\Models\Product;
use App\Services\Shared\ImageService;

/**
 * Shared product repository used by both Admin and Vendor layers.
 *
 * store() and update() are dead code — controllers bypass them and use Eloquent
 * directly inside DB transactions for full control over variants and attribute
 * values. They are retained here only to satisfy the interface contract.
 * Only destroy() is called at runtime (via ProductService::destroy()).
 */
class ProductRepository implements ProductRepositoryInterface
{
    protected ImageService $imageService;

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

    /**
     * Dead code — not called by any controller at runtime.
     *
     * @see ProductRepositoryInterface for rationale.
     */
    public function store(array $data)
    {
        // Intentionally not implemented — controllers handle product creation directly.
        throw new \LogicException('ProductRepository::store() is not used. Create products via the controller transaction.');
    }

    /**
     * Dead code — not called by any controller at runtime.
     *
     * @see ProductRepositoryInterface for rationale.
     */
    public function update($id, array $data)
    {
        // Intentionally not implemented — controllers handle product updates directly.
        throw new \LogicException('ProductRepository::update() is not used. Update products via the controller transaction.');
    }

    public function destroy($id)
    {
        $product = $this->find($id);

        foreach ($product->images as $image) {
            if ($image->image_url) {
                $this->imageService->deleteImage($image->image_url);
            }
            $image->delete();
        }

        return $product->delete();
    }
}
