@extends('themes.xylo.layouts.master')

@section('content')
    <div class="container xsf-recipe-page">
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
                    <div class="text-success" style="font-size: 1.25rem;">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    </div>
                    <div>
                        <p class="fw-semibold mb-0 text-dark">Full recipe unlocked</p>
                        <p class="text-secondary small mb-0">
                            @if($order)
                                Included with Order #{{ $order->id }} &bull; Thank you for your purchase!
                            @else
                                You have full access to view and download this recipe.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="xsf-unlocked-banner__actions">
                    @php
                        $downloadUrl = route('recipes.download-pdf', array_filter([
                            'slug'     => $recipe->slug,
                            'order_id' => $order?->id,
                            'token'    => $order ? app(\App\Services\Store\RecipeAccessService::class)->generateOrderToken($order) : null,
                        ]));
                    @endphp
                    <a href="{{ $downloadUrl }}" class="btn btn-primary">
                        <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-dark" onclick="window.print();">
                        <i class="fa-solid fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        @endif

        {{-- Intro: single readable column --}}
        <header class="xsf-recipe-intro">
            <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="xsf-recipe-hero-img" onerror="this.src='https://placehold.co/800x500/eee/999?text=Recipe';">
            @if($recipe->cuisine)
                <p class="xsf-recipe-eyebrow">{{ $recipe->cuisine }} cuisine</p>
            @endif
            <h1 class="xsf-recipe-title">{{ $recipe->title }}</h1>

            @if($recipe->summary)
                <p class="xsf-recipe-lead">{{ $recipe->summary }}</p>
            @endif

            {{-- Meta row --}}
            <div class="xsf-recipe-meta">
                <div class="xsf-recipe-meta-pill">
                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i>Prep</span>
                    <strong>{{ $recipe->prep_time ? $recipe->prep_time . 'm' : '15m' }}</strong>
                </div>
                <div class="xsf-recipe-meta-pill">
                    <span><i class="fa-solid fa-fire-burner" aria-hidden="true"></i>Cook</span>
                    <strong>{{ $recipe->cook_time ? $recipe->cook_time . 'm' : '20m' }}</strong>
                </div>
                <div class="xsf-recipe-meta-pill">
                    <span><i class="fa-solid fa-users" aria-hidden="true"></i>Serves</span>
                    <strong>{{ $recipe->servings ?? 2 }}</strong>
                </div>
                <div class="xsf-recipe-meta-pill">
                    <span><i class="fa-solid fa-gauge-high" aria-hidden="true"></i>Difficulty</span>
                    <strong>{{ ucfirst($recipe->difficulty ?? 'Medium') }}</strong>
                </div>
                @if($recipe->calories)
                    <div class="xsf-recipe-meta-pill">
                        <span><i class="fa-solid fa-fire" aria-hidden="true"></i>Calories</span>
                        <strong>{{ $recipe->calories }} kcal</strong>
                    </div>
                @endif
            </div>

            @if(!$isUnlocked)
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-muted" style="font-size: 1.25rem;">
                            <i class="fa-solid fa-gift" aria-hidden="true"></i>
                        </div>
                        <div class="flex-fill">
                            <span class="fw-semibold text-dark d-block">Bonus recipe</span>
                            <span class="text-secondary small">Buy any linked product to unlock the full step-by-step directions and a downloadable PDF card.</span>
                        </div>
                    </div>
                </div>
            @endif
        </header>

        {{-- Main Recipe Body --}}
        <div class="xsf-recipe-body">
            {{-- Aside: Ingredients, products & nutrition --}}
            <aside class="xsf-recipe-body__aside">
                {{-- Ingredients Card --}}
                <div class="xsf-recipe-block">
                    <h2 class="xsf-recipe-block__title">
                        <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                        Ingredients
                    </h2>
                    <p class="xsf-recipe-block__hint">Tick ingredients off as you cook.</p>

                    @if(!empty($recipe->ingredients) && is_array($recipe->ingredients))
                        <div class="xsf-ingredients-list" role="group" aria-label="Ingredients">
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
                            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                            Eligible Store Products
                        </h2>
                        <p class="xsf-recipe-block__hint">
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
                                    <div class="xsf-linked-product__thumb d-flex align-items-center justify-content-center text-muted" aria-hidden="true">
                                        <i class="fa-solid fa-box"></i>
                                    </div>
                                @endif
                                <div class="flex-fill min-w-0">
                                    <a href="{{ route('product.show', $p->slug) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block small">
                                        {{ $p->name }}
                                    </a>
                                    <span class="text-secondary fw-semibold small">
                                        {{ $currency->symbol }}{{ number_format($p->price, 2) }}
                                    </span>
                                </div>
                                <a href="{{ route('product.show', $p->slug) }}" class="btn btn-outline-secondary btn-sm" title="View product" aria-label="View {{ $p->name }}">
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
                            <i class="fa-solid fa-heart-pulse" aria-hidden="true"></i>
                            Nutritional Info
                        </h2>
                        <table class="xsf-nutrition">
                            <tbody>
                                @foreach($recipe->nutritional_info as $nutrient => $val)
                                    <tr>
                                        <th scope="row">{{ $nutrient }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </aside>

            {{-- Main: Step-by-Step Directions / Gated State --}}
            <div class="xsf-recipe-body__main">
                <div class="xsf-recipe-block">
                    <h2 class="xsf-recipe-block__title">
                        <i class="fa-solid fa-kitchen-set" aria-hidden="true"></i>
                        Step-by-Step Directions
                    </h2>

                    @if($isUnlocked)
                        {{-- UNLOCKED: Full Instructions --}}
                        @if(!empty($recipe->instructions) && is_array($recipe->instructions))
                            <ol class="xsf-steps-container list-unstyled mb-0">
                                @foreach($recipe->instructions as $index => $step)
                                    @php
                                        $stepNum = $step['step'] ?? ($index + 1);
                                        $stepTitle = $step['title'] ?? null;
                                        $stepText = $step['text'] ?? ($step['description'] ?? (is_string($step) ? $step : ''));
                                    @endphp
                                    <li class="xsf-step-row">
                                        <div class="xsf-step-num" aria-hidden="true">{{ $stepNum }}</div>
                                        <div class="xsf-step-text">
                                            <span class="visually-hidden">Step {{ $stepNum }}.</span>
                                            @if($stepTitle)
                                                <h3 class="xsf-step-title">{{ $stepTitle }}</h3>
                                            @endif
                                            <p class="mb-0">{{ $stepText }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        @else
                            <p class="text-muted">Detailed culinary steps will be displayed here.</p>
                        @endif

                        @if($recipe->content)
                            <div class="mt-4 p-3 bg-light rounded-3 border">
                                <h3 class="h6 fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-lightbulb me-1" aria-hidden="true"></i> Chef's Notes & Pro Tips
                                </h3>
                                <div class="text-secondary small line-height-lg">
                                    {!! nl2br(e($recipe->content)) !!}
                                </div>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="mt-4 pt-3 border-top d-flex gap-2 flex-wrap no-print">
                            <a href="{{ $downloadUrl }}" class="btn btn-primary">
                                <i class="fa-solid fa-file-pdf me-2"></i> Download Recipe PDF
                            </a>
                            <button type="button" class="btn btn-outline-dark" onclick="window.print();">
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
                                <ol class="list-unstyled mb-0">
                                    <li class="xsf-step-row">
                                        <div class="xsf-step-num" aria-hidden="true">1</div>
                                        <div class="xsf-step-text">
                                            <span class="visually-hidden">Step 1.</span>
                                            @if(!empty($firstStep['title']))
                                                <h3 class="xsf-step-title">{{ $firstStep['title'] }}</h3>
                                            @endif
                                            <p class="mb-0">{{ $firstStepText }}</p>
                                        </div>
                                    </li>
                                </ol>
                            @endif
                        @endif

                        <div class="xsf-locked-card">
                            <div class="xsf-lock-icon">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            </div>
                            <h3 class="h5 fw-semibold text-dark mb-2">Unlock the full recipe and PDF</h3>
                            <p class="text-muted small max-w-lg mx-auto mb-4">
                                The full step-by-step culinary instructions and printable card are exclusively unlocked when you purchase any eligible product from our store!
                            </p>

                            @if($recipe->products->isNotEmpty())
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    @php $firstProduct = $recipe->products->first(); @endphp
                                    <a href="{{ route('product.show', $firstProduct->slug) }}" class="btn btn-primary">
                                        <i class="fa-solid fa-bag-shopping me-1"></i> Buy {{ $firstProduct->name }}
                                    </a>
                                    <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary">
                                        Browse Shop
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('shop.index') }}" class="btn btn-primary">
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
