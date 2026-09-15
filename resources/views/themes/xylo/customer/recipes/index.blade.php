@extends('themes.xylo.layouts.master')

@section('css')
<style>
.xsf-account-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.xsf-unlocked-recipe-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}

.xsf-unlocked-recipe-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.xsf-unlocked-recipe-row:first-child {
    padding-top: 0;
}

.xsf-unlocked-recipe-thumb {
    width: 68px;
    height: 68px;
    border-radius: 12px;
    object-fit: cover;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}

@media (max-width: 575.98px) {
    .xsf-account-card {
        padding: 16px;
    }
    .xsf-unlocked-recipe-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .xsf-unlocked-recipe-row .d-flex.align-items-center.gap-2.flex-shrink-0 {
        width: 100%;
    }
    .xsf-unlocked-recipe-row .btn {
        flex: 1;
        text-align: center;
    }
}
</style>
@endsection

@section('content')
<section class="xsf-section py-4">
    <div class="container">
        {{-- Top Navigation & Title --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-3 border-bottom">
            <div>
                <a href="{{ route('customer.profile.edit') }}" class="btn btn-outline-secondary btn-pill btn-sm mb-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Account
                </a>
                <h1 class="h4 fw-bold text-dark mb-0">My Unlocked Recipes</h1>
                <p class="text-muted small mb-0 mt-1">Exclusive chef guides and cards unlocked with your purchases.</p>
            </div>
            <div>
                <a href="{{ route('recipes.index') }}" class="btn btn-outline-primary btn-pill btn-sm">
                    <i class="fa-solid fa-utensils me-1"></i> Browse All Recipes
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="xsf-account-card">
                    @if($recipes->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted" style="font-size: 3rem;">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <h3 class="h5 fw-bold text-dark">No Unlocked Recipes Yet</h3>
                            <p class="text-muted small max-w-md mx-auto mb-4">
                                When you complete an order containing products with bonus recipes, they will appear here in your permanent library for instant viewing and PDF download!
                            </p>
                            <a href="{{ route('recipes.index') }}" class="btn btn-primary btn-pill btn-sm px-4">
                                Discover Recipes & Eligible Products
                            </a>
                        </div>
                    @else
                        <div class="xsf-unlocked-recipes-list">
                            @foreach($recipes as $recipe)
                                <div class="xsf-unlocked-recipe-row">
                                    <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="xsf-unlocked-recipe-thumb" onerror="this.src='https://placehold.co/100x100/eee/999?text=Recipe';">

                                    <div class="flex-fill min-w-0">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            @if($recipe->cuisine)
                                                <span class="badge bg-primary-subtle text-primary text-uppercase" style="font-size: 0.68rem;">{{ $recipe->cuisine }}</span>
                                            @endif
                                            <span class="badge {{ $recipe->difficulty_badge_class }}" style="font-size: 0.68rem;">{{ ucfirst($recipe->difficulty) }}</span>
                                        </div>
                                        <h2 class="h6 fw-bold mb-1">
                                            <a href="{{ route('recipes.show', $recipe->slug) }}" class="text-dark text-decoration-none">
                                                {{ $recipe->title }}
                                            </a>
                                        </h2>
                                        <p class="text-muted small mb-0 text-truncate">
                                            {{ $recipe->summary ?: 'Handcrafted recipe guide unlocked with your order.' }}
                                        </p>
                                        <div class="text-muted small mt-1">
                                            <i class="fa-regular fa-clock me-1"></i> {{ $recipe->total_time ? $recipe->total_time . ' min' : '30 min' }} &bull; {{ $recipe->servings ?? 2 }} servings
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-dark btn-sm btn-pill">
                                            <i class="fa-solid fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('recipes.download-pdf', $recipe->slug) }}" class="btn btn-primary btn-sm btn-pill">
                                            <i class="fa-solid fa-file-pdf me-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
