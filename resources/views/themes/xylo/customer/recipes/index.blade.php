@extends('themes.xylo.layouts.master')

@section('content')
<section class="xsf-section">
    <div class="container">
        <div class="xsf-account-layout">
            <div class="xsf-account-layout__aside">
                @include('themes.xylo.customer._nav', ['active' => 'recipes'])
            </div>

            <div class="xsf-account-layout__main">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 xsf-account-head">
                    <div>
                        <h1>My Unlocked Recipes</h1>
                        <p>Chef guides and recipe cards unlocked with your purchases.</p>
                    </div>
                    <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-utensils me-1" aria-hidden="true"></i> Browse all recipes
                    </a>
                </div>

                @if($recipes->isEmpty())
                    <div class="xsf-empty">
                        <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                        <h2 class="h5">No unlocked recipes yet</h2>
                        <p class="mb-3">Orders that include products with bonus recipes will unlock them here for viewing and PDF download.</p>
                        <a href="{{ route('recipes.index') }}" class="btn btn-primary btn-sm">Discover recipes and eligible products</a>
                    </div>
                @else
                    <div class="xsf-account-card">
                        <div class="xsf-account-card__body xsf-unlocked-recipes-list">
                            @foreach($recipes as $recipe)
                                <div class="xsf-recipe-row">
                                    <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="xsf-recipe-row__thumb" loading="lazy" onerror="this.src='https://placehold.co/100x100/eee/999?text=Recipe';">

                                    <div class="xsf-recipe-row__body">
                                        <h2 class="h6 fw-semibold mb-1">
                                            <a href="{{ route('recipes.show', $recipe->slug) }}" class="xsf-recipe-row__title">{{ $recipe->title }}</a>
                                        </h2>
                                        <div class="xsf-recipe-row__meta">
                                            @if($recipe->cuisine){{ $recipe->cuisine }} &bull; @endif{{ ucfirst($recipe->difficulty) }} &bull; <i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $recipe->total_time ? $recipe->total_time . ' min' : '30 min' }} &bull; {{ $recipe->servings ?? 2 }} servings
                                        </div>
                                        <p class="xsf-recipe-row__summary">
                                            {{ $recipe->summary ?: 'Handcrafted recipe guide unlocked with your order.' }}
                                        </p>
                                    </div>

                                    <div class="xsf-recipe-row__actions">
                                        <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-dark btn-sm">
                                            <i class="fa-solid fa-eye me-1" aria-hidden="true"></i> View
                                        </a>
                                        <a href="{{ route('recipes.download-pdf', $recipe->slug) }}" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-file-pdf me-1" aria-hidden="true"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
