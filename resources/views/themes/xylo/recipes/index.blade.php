@extends('themes.xylo.layouts.master')

@section('content')
    <div class="container">
        <header class="xsf-recipes-head">
            <h1>Recipes</h1>
            <p>Handcrafted recipes from expert chefs. Buy an eligible product to unlock the step-by-step guide and a downloadable recipe card.</p>
        </header>
    </div>

    <div class="container pb-5">
        {{-- Filter & Search Bar --}}
        <form action="{{ route('recipes.index') }}" method="GET" class="xsf-recipe-filter-bar" role="search">
            <label class="xsf-recipe-filter-bar__search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <span class="visually-hidden">Search recipes</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name, ingredient or cuisine">
            </label>
            <div class="xsf-recipe-filter-bar__chips">
                <a href="{{ route('recipes.index') }}" class="xsf-recipe-filter-pill {{ !request('cuisine') && !request('difficulty') ? 'active' : '' }}" @if(!request('cuisine') && !request('difficulty')) aria-current="true" @endif>
                    All Recipes
                </a>
                @foreach($cuisines as $c)
                    <a href="{{ route('recipes.index', array_merge(request()->query(), ['cuisine' => $c])) }}" class="xsf-recipe-filter-pill {{ request('cuisine') === $c ? 'active' : '' }}" @if(request('cuisine') === $c) aria-current="true" @endif>
                        {{ $c }}
                    </a>
                @endforeach
                <select name="difficulty" aria-label="Difficulty" onchange="this.form.submit()">
                    <option value="">All Difficulties</option>
                    <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>
        </form>

        {{-- Recipes Grid --}}
        @if($recipes->isEmpty())
            <div class="xsf-empty">
                <i class="fa-solid fa-cookie-bite" aria-hidden="true"></i>
                <h2 class="h5">No recipes found</h2>
                <p class="mb-3">Try a different keyword or remove your filters.</p>
                <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary btn-sm">Clear filters</a>
            </div>
        @else
            <div class="xsf-recipe-grid">
                @foreach($recipes as $recipe)
                    @php
                        $isUnlocked = in_array($recipe->id, $unlockedRecipeIds);
                    @endphp
                    <article class="xsf-recipe-card">
                            <div class="xsf-recipe-card__img-wrap">
                                <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="xsf-recipe-card__img" loading="lazy" onerror="this.src='https://placehold.co/600x400/eee/999?text=Recipe';">

                                @if($isUnlocked)
                                    <span class="xsf-recipe-card__unlocked-badge">
                                        <i class="fa-solid fa-circle-check"></i> Unlocked
                                    </span>
                                @endif

                                <span class="xsf-recipe-card__difficulty">
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
                                    <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-dark btn-sm flex-fill">
                                        {{ $isUnlocked ? 'View Recipe' : 'View Preview' }}
                                    </a>
                                    @if($isUnlocked)
                                        <a href="{{ route('recipes.download-pdf', $recipe->slug) }}" class="btn btn-primary btn-sm" title="Download PDF" aria-label="Download PDF for {{ $recipe->title }}">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-5 d-flex justify-content-center">
                {{ $recipes->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
