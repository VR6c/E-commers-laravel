@extends('themes.xylo.layouts.master')

@section('css')
<style>
/* ─────────────────────────────────────────────
   Recipe Detail & Interactive Cooking Page
   ───────────────────────────────────────────── */
.xsf-recipe-header {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 32px 0 24px;
    margin-bottom: 32px;
}

.xsf-recipe-hero-img {
    width: 100%;
    height: 380px;
    object-fit: cover;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

.xsf-recipe-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin-bottom: 12px;
    line-height: 1.25;
}

.xsf-recipe-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
}

.xsf-recipe-meta-pill i {
    color: #6366f1;
}

/* ── Card Blocks ── */
.xsf-recipe-block {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 28px;
    margin-bottom: 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.xsf-recipe-block__title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ── Interactive Ingredients Checklist ── */
.xsf-ingredient-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px dashed #e2e8f0;
    cursor: pointer;
    user-select: none;
    transition: background 0.15s ease;
}

.xsf-ingredient-item:last-child {
    border-bottom: none;
}

.xsf-ingredient-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    border-radius: 5px;
    cursor: pointer;
}

.xsf-ingredient-item.is-checked {
    text-decoration: line-through;
    color: #94a3b8;
}

/* ── Step-by-Step Directions ── */
.xsf-step-row {
    display: flex;
    gap: 18px;
    padding-bottom: 24px;
    margin-bottom: 24px;
    border-bottom: 1px solid #f1f5f9;
}

.xsf-step-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.xsf-step-num {
    width: 36px;
    height: 36px;
    background: #0f172a;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
}

.xsf-step-text {
    flex: 1;
    font-size: 0.95rem;
    line-height: 1.65;
    color: #334155;
}

/* ── Gated Lock Overlay ── */
.xsf-locked-card {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px dashed #cbd5e1;
    border-radius: 20px;
    padding: 40px 32px;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-top: 20px;
}

.xsf-lock-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin: 0 auto 16px;
}

/* ── Product Card in Recipe ── */
.xsf-linked-product {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 12px;
    background: #ffffff;
    transition: all 0.2s ease;
}

.xsf-linked-product:hover {
    border-color: #6366f1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.xsf-linked-product__thumb {
    width: 52px;
    height: 52px;
    object-fit: cover;
    border-radius: 8px;
    background: #f1f5f9;
}

/* ── Unlocked Success Banner ── */
.xsf-unlocked-banner {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}

/* ── Mobile & Tablet Responsiveness ── */
@media (max-width: 991.98px) {
    .xsf-recipe-hero-img {
        height: 280px;
    }
    .xsf-recipe-title {
        font-size: 1.8rem;
    }
    .xsf-recipe-block {
        padding: 20px;
        border-radius: 14px;
    }
}

@media (max-width: 575.98px) {
    .xsf-recipe-hero-img {
        height: 220px;
        border-radius: 14px;
    }
    .xsf-recipe-title {
        font-size: 1.45rem;
    }
    .xsf-recipe-meta-pill {
        font-size: 0.78rem;
        padding: 6px 12px;
    }
    .xsf-step-row {
        gap: 12px;
    }
    .xsf-step-num {
        width: 30px;
        height: 30px;
        font-size: 0.82rem;
    }
    .xsf-step-text {
        font-size: 0.88rem;
    }
    .xsf-unlocked-banner {
        flex-direction: column;
        align-items: flex-start;
        padding: 14px;
    }
    .xsf-unlocked-banner .d-flex.align-items-center.gap-2 {
        width: 100%;
        margin-top: 6px;
    }
    .xsf-unlocked-banner .btn {
        flex: 1;
        text-align: center;
    }
    .xsf-locked-card {
        padding: 28px 16px;
    }
}

/* ── Dedicated Print Layout ── */
@media print {
    @page {
        size: A5 portrait;
        margin: 8mm 10mm;
    }

    .xsf-header,
    .xsf-footer,
    .xsf-breadcrumb,
    .btn,
    .xsf-unlocked-banner,
    .no-print {
        display: none !important;
    }

    body {
        background: #ffffff !important;
        font-size: 8.5pt !important;
        color: #000000 !important;
    }

    .xsf-recipe-hero-img {
        max-height: 180px !important;
        border-radius: 8px !important;
    }

    .xsf-recipe-block {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin-bottom: 14px !important;
    }

    .xsf-step-num {
        background: #000 !important;
        color: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
@endsection

@section('content')
    <div class="container py-4">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="xsf-breadcrumb mb-3">
            <a href="{{ route('xylo.home') }}">Home</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <a href="{{ route('recipes.index') }}">Recipes</a>
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span>{{ $recipe->title }}</span>
        </nav>

        {{-- Unlocked Top Notice --}}
        @if($isUnlocked)
            <div class="xsf-unlocked-banner">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-success" style="font-size: 1.5rem;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <h4 class="h6 fw-bold mb-0 text-dark">Full Recipe Unlocked</h4>
                        <p class="text-muted small mb-0">
                            @if($order)
                                Included with Order #{{ $order->id }} &bull; Thank you for your purchase!
                            @else
                                You have full access to view and download this recipe.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @php
                        $downloadUrl = route('recipes.download-pdf', array_filter([
                            'slug'     => $recipe->slug,
                            'order_id' => $order?->id,
                            'token'    => $order ? app(\App\Services\Store\RecipeAccessService::class)->generateOrderToken($order) : null,
                        ]));
                    @endphp
                    <a href="{{ $downloadUrl }}" class="btn btn-primary btn-pill btn-sm px-3">
                        <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-dark btn-pill btn-sm px-3" onclick="window.print();">
                        <i class="fa-solid fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        @endif

        {{-- Top Header / Hero Grid --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="xsf-recipe-hero-img" onerror="this.src='https://placehold.co/800x500/eee/999?text=Recipe';">
            </div>
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                @if($recipe->cuisine)
                    <span class="text-uppercase fw-bold text-primary small mb-2 letter-spacing-1">{{ $recipe->cuisine }} Cuisine</span>
                @endif
                <h1 class="xsf-recipe-title">{{ $recipe->title }}</h1>

                @if($recipe->summary)
                    <p class="text-secondary mb-4 line-height-lg">{{ $recipe->summary }}</p>
                @endif

                {{-- Meta Chips --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <div class="xsf-recipe-meta-pill">
                        <i class="fa-regular fa-clock"></i>
                        <span>Prep: <strong>{{ $recipe->prep_time ? $recipe->prep_time . 'm' : '15m' }}</strong></span>
                    </div>
                    <div class="xsf-recipe-meta-pill">
                        <i class="fa-solid fa-fire-burner"></i>
                        <span>Cook: <strong>{{ $recipe->cook_time ? $recipe->cook_time . 'm' : '20m' }}</strong></span>
                    </div>
                    <div class="xsf-recipe-meta-pill">
                        <i class="fa-solid fa-users"></i>
                        <span>Serves: <strong>{{ $recipe->servings ?? 2 }}</strong></span>
                    </div>
                    <div class="xsf-recipe-meta-pill">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Difficulty: <strong>{{ ucfirst($recipe->difficulty ?? 'Medium') }}</strong></span>
                    </div>
                    @if($recipe->calories)
                        <div class="xsf-recipe-meta-pill">
                            <i class="fa-solid fa-fire"></i>
                            <span><strong>{{ $recipe->calories }}</strong> kcal</span>
                        </div>
                    @endif
                </div>

                @if(!$isUnlocked)
                    <div class="p-3 bg-light rounded-4 border">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-primary" style="font-size: 1.4rem;">
                                <i class="fa-solid fa-gift"></i>
                            </div>
                            <div class="flex-fill">
                                <span class="fw-bold text-dark d-block">Exclusive Bonus Recipe</span>
                                <span class="text-muted small">Buy any linked product to unlock full step-by-step directions & downloadable PDF card.</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Main Recipe Body --}}
        <div class="row g-4">
            {{-- Left Column: Ingredients & Nutritional Info --}}
            <div class="col-lg-4">
                {{-- Ingredients Card --}}
                <div class="xsf-recipe-block">
                    <h2 class="xsf-recipe-block__title">
                        <i class="fa-solid fa-basket-shopping text-primary"></i>
                        Ingredients
                    </h2>
                    <p class="text-muted small mb-3">Click on any ingredient to mark it off while cooking:</p>

                    @if(!empty($recipe->ingredients) && is_array($recipe->ingredients))
                        <div class="xsf-ingredients-list">
                            @foreach($recipe->ingredients as $index => $ing)
                                <label class="xsf-ingredient-item" for="ing-{{ $index }}">
                                    <input type="checkbox" id="ing-{{ $index }}" onchange="this.parentElement.classList.toggle('is-checked', this.checked)">
                                    <span class="fw-semibold text-dark">{{ $ing['quantity'] ?? '' }} {{ $ing['unit'] ?? '' }}</span>
                                    <span>{{ $ing['item'] ?? ($ing['name'] ?? 'Ingredient') }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small">Ingredients will be displayed here.</p>
                    @endif
                </div>

                {{-- Linked Store Products Card --}}
                @if($recipe->products->isNotEmpty())
                    <div class="xsf-recipe-block">
                        <h2 class="xsf-recipe-block__title">
                            <i class="fa-solid fa-bag-shopping text-success"></i>
                            Eligible Store Products
                        </h2>
                        <p class="text-muted small mb-3">
                            @if($isUnlocked)
                                Products associated with this recipe:
                            @else
                                Purchase any of these products to unlock the full recipe & PDF:
                            @endif
                        </p>

                        @foreach($recipe->products as $p)
                            @php
                                $thumbUrl = $p->thumbnail?->image_url
                                    ? product_image_url($p->thumbnail->image_url)
                                    : ($p->image_url ? product_image_url($p->image_url) : null);
                            @endphp
                            <div class="xsf-linked-product">
                                @if($thumbUrl)
                                    <img src="{{ $thumbUrl }}" alt="{{ $p->name }}" class="xsf-linked-product__thumb">
                                @else
                                    <div class="xsf-linked-product__thumb d-flex align-items-center justify-content-center text-muted">
                                        <i class="fa-solid fa-box"></i>
                                    </div>
                                @endif
                                <div class="flex-fill min-w-0">
                                    <a href="{{ route('product.show', $p->slug) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block small">
                                        {{ $p->name }}
                                    </a>
                                    <span class="text-primary fw-bold small">
                                        {{ $currency->symbol }}{{ number_format($p->price, 2) }}
                                    </span>
                                </div>
                                <a href="{{ route('product.show', $p->slug) }}" class="btn btn-outline-primary btn-sm btn-pill px-2" title="View Product">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Nutritional Info Card --}}
                @if(!empty($recipe->nutritional_info) && is_array($recipe->nutritional_info))
                    <div class="xsf-recipe-block">
                        <h2 class="xsf-recipe-block__title">
                            <i class="fa-solid fa-heart-pulse text-danger"></i>
                            Nutritional Info
                        </h2>
                        <div class="row g-2 text-center">
                            @foreach($recipe->nutritional_info as $nutrient => $val)
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-3">
                                        <span class="d-block text-muted small text-uppercase">{{ $nutrient }}</span>
                                        <span class="fw-bold text-dark">{{ $val }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Step-by-Step Directions / Gated State --}}
            <div class="col-lg-8">
                <div class="xsf-recipe-block">
                    <h2 class="xsf-recipe-block__title">
                        <i class="fa-solid fa-kitchen-set text-warning"></i>
                        Step-by-Step Directions
                    </h2>

                    @if($isUnlocked)
                        {{-- UNLOCKED: Full Instructions --}}
                        @if(!empty($recipe->instructions) && is_array($recipe->instructions))
                            <div class="xsf-steps-container">
                                @foreach($recipe->instructions as $index => $step)
                                    @php
                                        $stepNum = $step['step'] ?? ($index + 1);
                                        $stepTitle = $step['title'] ?? null;
                                        $stepText = $step['text'] ?? ($step['description'] ?? (is_string($step) ? $step : ''));
                                    @endphp
                                    <div class="xsf-step-row">
                                        <div class="xsf-step-num">{{ $stepNum }}</div>
                                        <div class="xsf-step-text">
                                            @if($stepTitle)
                                                <h3 class="h6 fw-bold text-dark mb-1">{{ $stepTitle }}</h3>
                                            @endif
                                            <p class="mb-0">{{ $stepText }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Detailed culinary steps will be displayed here.</p>
                        @endif

                        @if($recipe->content)
                            <div class="mt-4 p-3 bg-light rounded-4 border">
                                <h3 class="h6 fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-lightbulb text-warning me-1"></i> Chef's Notes & Pro Tips
                                </h3>
                                <div class="text-secondary small line-height-lg">
                                    {!! nl2br(e($recipe->content)) !!}
                                </div>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="mt-4 pt-3 border-top d-flex gap-3 flex-wrap">
                            <a href="{{ $downloadUrl }}" class="btn btn-primary btn-pill px-4">
                                <i class="fa-solid fa-file-pdf me-2"></i> Download Recipe PDF
                            </a>
                            <button type="button" class="btn btn-outline-dark btn-pill px-3" onclick="window.print();">
                                <i class="fa-solid fa-print me-2"></i> Print Recipe
                            </button>
                        </div>
                    @else
                        {{-- LOCKED: Preview & CTA --}}
                        @if(!empty($recipe->instructions) && is_array($recipe->instructions))
                            {{-- Show Step 1 as teaser --}}
                            @php
                                $firstStep = $recipe->instructions[0] ?? null;
                                $firstStepText = $firstStep['text'] ?? ($firstStep['description'] ?? (is_string($firstStep) ? $firstStep : ''));
                            @endphp
                            @if($firstStepText)
                                <div class="xsf-step-row">
                                    <div class="xsf-step-num">1</div>
                                    <div class="xsf-step-text">
                                        @if(!empty($firstStep['title']))
                                            <h3 class="h6 fw-bold text-dark mb-1">{{ $firstStep['title'] }}</h3>
                                        @endif
                                        <p class="mb-0">{{ $firstStepText }}</p>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="xsf-locked-card">
                            <div class="xsf-lock-icon">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <h3 class="h5 fw-bold text-dark mb-2">Unlock Full Recipe & PDF Download</h3>
                            <p class="text-muted small max-w-lg mx-auto mb-4">
                                The full step-by-step culinary instructions and printable card are exclusively unlocked when you purchase any eligible product from our store!
                            </p>

                            @if($recipe->products->isNotEmpty())
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    @php $firstProduct = $recipe->products->first(); @endphp
                                    <a href="{{ route('product.show', $firstProduct->slug) }}" class="btn btn-primary btn-pill px-4">
                                        <i class="fa-solid fa-bag-shopping me-1"></i> Buy {{ $firstProduct->name }}
                                    </a>
                                    <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary btn-pill px-3">
                                        Browse Shop
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-pill px-4">
                                    Shop Eligible Products
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
