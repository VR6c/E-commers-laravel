<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

final class ProductSearch extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $category_id = null;
    public ?int $brand_id = null;
    public ?float $min_price = null;
    public ?float $max_price = null;
    public string $sort_by = 'newest';
    public int $per_page = 12;

    /**
     * Bind component parameters to URL query string.
     */
    protected array $queryString = [
        'search'      => ['except' => ''],
        'category_id' => ['except' => null, 'as' => 'category'],
        'brand_id'    => ['except' => null, 'as' => 'brand'],
        'min_price'   => ['except' => null, 'as' => 'min'],
        'max_price'   => ['except' => null, 'as' => 'max'],
        'sort_by'     => ['except' => 'newest', 'as' => 'sort'],
        'per_page'    => ['except' => 12, 'as' => 'per_page'],
    ];

    /**
     * Reset pagination to page 1 whenever search query or filters are updated.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingBrandId(): void
    {
        $this->resetPage();
    }

    public function updatingMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatingMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Reset all active filters to default state.
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'category_id', 'brand_id', 'min_price', 'max_price', 'sort_by']);
        $this->resetPage();
        $this->dispatch('toast', type: 'info', message: 'All filters have been cleared.');
    }

    /**
     * Remove individual filter property.
     */
    public function removeFilter(string $filter): void
    {
        if (in_array($filter, ['search', 'category_id', 'brand_id', 'min_price', 'max_price'], true)) {
            $this->reset($filter);
            $this->resetPage();
            $this->dispatch('toast', type: 'info', message: ucfirst(str_replace('_', ' ', $filter)) . ' filter removed.');
        }
    }

    public function render(): View
    {
        $query = Product::query()
            ->with(['category', 'brand', 'images', 'primaryVariant', 'variants']);

        // Search filter (matches product name, description, short_description, tags, or variant SKU)
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm): void {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('short_description', 'like', $searchTerm)
                  ->orWhere('tags', 'like', $searchTerm)
                  ->orWhereHas('variants', fn (Builder $v) => $v->where('SKU', 'like', $searchTerm));
            });
        }

        // Category filter
        if ($this->category_id !== null) {
            $query->where('category_id', $this->category_id);
        }

        // Brand filter
        if ($this->brand_id !== null) {
            $query->where('brand_id', $this->brand_id);
        }

        // Price range filters (via product variants)
        if ($this->min_price !== null && $this->min_price > 0) {
            $query->whereHas('variants', function (Builder $v): void {
                $v->where('price', '>=', $this->min_price);
            });
        }

        if ($this->max_price !== null && $this->max_price > 0) {
            $query->whereHas('variants', function (Builder $v): void {
                $v->where('price', '<=', $this->max_price);
            });
        }

        // Sorting options
        match ($this->sort_by) {
            'price_asc'  => $query->select('products.*')
                                  ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                                  ->whereRaw('product_variants.is_primary is true')
                                  ->orderBy('product_variants.price', 'asc'),
            'price_desc' => $query->select('products.*')
                                  ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                                  ->whereRaw('product_variants.is_primary is true')
                                  ->orderBy('product_variants.price', 'desc'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            default      => $query->latest('products.created_at'),
        };

        $products   = $query->paginate($this->per_page);
        $categories = \Illuminate\Support\Facades\Cache::remember('search_categories', 3600, fn () => Category::query()->select(['id', 'name', 'slug'])->orderBy('name')->get());
        $brands     = \Illuminate\Support\Facades\Cache::remember('search_brands', 3600, fn () => Brand::query()->select(['id', 'name', 'slug'])->orderBy('name')->get());

        return view('livewire.product-search', [
            'products'   => $products,
            'categories' => $categories,
            'brands'     => $brands,
        ]);
    }
}
