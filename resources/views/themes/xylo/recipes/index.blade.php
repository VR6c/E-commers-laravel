@extends('themes.xylo.layouts.master')

@section('css')
<style>
/* ─────────────────────────────────────────────
   Storefront Recipes Catalog Page
   ───────────────────────────────────────────── */
.xsf-recipe-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    padding: 56px 0 48px;
    margin-bottom: 40px;
    border-radius: 0 0 24px 24px;
    position: relative;
    overflow: hidden;
}

.xsf-recipe-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.xsf-recipe-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #cbd5e1;
    margin-bottom: 16px;
}

.xsf-recipe-hero__title {
    font-size: 2.3rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin-bottom: 12px;
}

.xsf-recipe-hero__lead {
    font-size: 1rem;
    color: #94a3b8;
    max-width: 620px;
    margin: 0;
    line-height: 1.6;
}

/* ── Filter Bar ── */
.xsf-recipe-filter-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 36px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.xsf-recipe-filter-pill {
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-block;
}

.xsf-recipe-filter-pill:hover,
.xsf-recipe-filter-pill.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

/* ── Recipe Card ── */
.xsf-recipe-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.xsf-recipe-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}

.xsf-recipe-card__img-wrap {
    position: relative;
    padding-top: 62%;
    overflow: hidden;
    background: #f1f5f9;
}

.xsf-recipe-card__img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
}

.xsf-recipe-card:hover .xsf-recipe-card__img {
    transform: scale(1.05);
}

.xsf-recipe-card__unlocked-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #16a34a;
    color: #ffffff;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35);
    z-index: 2;
}

.xsf-recipe-card__difficulty {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    z-index: 2;
}

.badge-easy { background: rgba(220, 252, 231, 0.95); color: #15803d; }
.badge-medium { background: rgba(254, 243, 199, 0.95); color: #92400e; }
.badge-hard { background: rgba(254, 226, 226, 0.95); color: #b91c1c; }

.xsf-recipe-card__body {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.xsf-recipe-card__cuisine {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6366f1;
    margin-bottom: 6px;
}

.xsf-recipe-card__title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px;
    line-height: 1.35;
}

.xsf-recipe-card__title a {
    color: inherit;
    text-decoration: none;
}

.xsf-recipe-card__title a:hover {
    color: #6366f1;
}

.xsf-recipe-card__summary {
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.xsf-recipe-card__meta {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
    color: #64748b;
}

.xsf-recipe-card__meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.xsf-recipe-card__actions {
    margin-top: 14px;
    display: flex;
    gap: 8px;
}

/* ── Mobile & Tablet Responsiveness ── */
@media (max-width: 768px) {
    .xsf-recipe-hero {
        padding: 36px 0 28px;
        border-radius: 0 0 16px 16px;
    }
    .xsf-recipe-hero__title {
        font-size: 1.65rem;
    }
    .xsf-recipe-filter-bar {
        padding: 14px 16px;
    }
    .xsf-recipe-card__body {
        padding: 16px;
    }
}
</style>
@endsection

@section('content')
    {{-- Hero Section --}}
    <section class="xsf-recipe-hero">
        <div class="container">
            <div class="xsf-recipe-hero__badge">
                <i class="fa-solid fa-utensils"></i> Chef's Kitchen Collection
            </div>
            <h1 class="xsf-recipe-hero__title">Curated Gourmet Recipes</h1>
            <p class="xsf-recipe-hero__lead">
                Explore handcrafted recipes created by expert chefs. Purchase eligible products or complete your checkout to unlock step-by-step guides and download official recipe cards.
            </p>
        </div>
    </section>

    <div class="container pb-5">
        {{-- Filter & Search Bar --}}
        <div class="xsf-recipe-filter-bar">
            <form action="{{ route('recipes.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Search recipes by name, ingredient, or cuisine...">
                    </div>
                </div>
                <div class="col-lg-7 d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
                    <a href="{{ route('recipes.index') }}" class="xsf-recipe-filter-pill {{ !request('cuisine') && !request('difficulty') ? 'active' : '' }}">
                        All Recipes
                    </a>
                    @foreach($cuisines as $c)
                        <a href="{{ route('recipes.index', array_merge(request()->query(), ['cuisine' => $c])) }}" class="xsf-recipe-filter-pill {{ request('cuisine') === $c ? 'active' : '' }}">
                            {{ $c }}
                        </a>
                    @endforeach
                    <select name="difficulty" class="form-select form-select-sm w-auto d-inline-block" onchange="this.form.submit()">
                        <option value="">All Difficulties</option>
                        <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
            </form>
        </div>

        {{-- Recipes Grid --}}
        @if($recipes->isEmpty())
            <div class="text-center py-5">
                <div class="mb-3 text-muted" style="font-size: 3rem;">
                    <i class="fa-solid fa-cookie-bite"></i>
                </div>
                <h3 class="h5 fw-bold text-dark">No Recipes Found</h3>
                <p class="text-muted small">Try searching with a different keyword or removing your filters.</p>
                <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary btn-pill btn-sm">Clear Filters</a>
            </div>
        @else
            <div class="row g-4">
                @foreach($recipes as $recipe)
                    @php
                        $isUnlocked = in_array($recipe->id, $unlockedRecipeIds);
                    @endphp
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="xsf-recipe-card">
                            <div class="xsf-recipe-card__img-wrap">
                                <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="xsf-recipe-card__img" onerror="this.src='https://placehold.co/600x400/eee/999?text=Recipe';">

                                @if($isUnlocked)
                                    <span class="xsf-recipe-card__unlocked-badge">
                                        <i class="fa-solid fa-circle-check"></i> Unlocked
                                    </span>
                                @endif

                                <span class="xsf-recipe-card__difficulty {{ $recipe->difficulty_badge_class }}">
                                    {{ $recipe->difficulty }}
                                </span>
                            </div>

                            <div class="xsf-recipe-card__body">
                                @if($recipe->cuisine)
                                    <div class="xsf-recipe-card__cuisine">{{ $recipe->cuisine }}</div>
                                @endif

                                <h2 class="xsf-recipe-card__title">
                                    <a href="{{ route('recipes.show', $recipe->slug) }}">{{ $recipe->title }}</a>
                                </h2>

                                <p class="xsf-recipe-card__summary">
                                    {{ $recipe->summary ?: 'Follow our step-by-step culinary instructions for an unforgettable homemade dining experience.' }}
                                </p>

                                <div class="xsf-recipe-card__meta">
                                    <div class="xsf-recipe-card__meta-item">
                                        <i class="fa-regular fa-clock"></i>
                                        <span>{{ $recipe->total_time ? $recipe->total_time . 'm' : '30m' }}</span>
                                    </div>
                                    <div class="xsf-recipe-card__meta-item">
                                        <i class="fa-solid fa-users"></i>
                                        <span>{{ $recipe->servings ?? 2 }} serv.</span>
                                    </div>
                                    @if($recipe->calories)
                                        <div class="xsf-recipe-card__meta-item">
                                            <i class="fa-solid fa-fire"></i>
                                            <span>{{ $recipe->calories }} kcal</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="xsf-recipe-card__actions">
                                    <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-dark btn-sm btn-pill flex-fill">
                                        {{ $isUnlocked ? 'View Recipe' : 'View Preview' }}
                                    </a>
                                    @if($isUnlocked)
                                        <a href="{{ route('recipes.download-pdf', $recipe->slug) }}" class="btn btn-primary btn-sm btn-pill" title="Download PDF">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 d-flex justify-content-center">
                {{ $recipes->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
