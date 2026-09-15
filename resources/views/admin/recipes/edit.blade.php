@extends('admin.layouts.admin')

@section('title', 'Edit Recipe - Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Recipe: ' . $recipe->title"
    icon="bi bi-pencil-square"
    :subtitle="'Modify recipe metadata, ingredients, directions, and linked products'"
    :breadcrumbs="['Recipes' => route('admin.recipes.index'), 'Edit' => '#']" />

<form action="{{ route('admin.recipes.update', $recipe->id) }}" method="POST" enctype="multipart/form-data" id="recipeEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Left Column: Primary Details & Repeaters --}}
        <div class="col-lg-8">
            <x-admin.data-card>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Recipe Details</h5>
                    <a href="{{ route('recipes.show', $recipe->slug) }}" target="_blank" class="btn btn-sm btn-outline-info btn-pill">
                        <i class="fas fa-external-link-alt me-1"></i> Preview on Store
                    </a>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Recipe Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $recipe->title) }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Short Summary</label>
                    <textarea name="summary" class="form-control @error('summary') is-invalid @enderror" rows="2">{{ old('summary', $recipe->summary) }}</textarea>
                    @error('summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Chef's Notes & Full Details</label>
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="4">{{ old('content', $recipe->content) }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </x-admin.data-card>

            {{-- Ingredients Repeater --}}
            <x-admin.data-card class="mt-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-basket text-primary me-1"></i> Ingredients List
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-pill" id="addIngredientBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Ingredient
                    </button>
                </div>

                <div id="ingredientsContainer">
                    @php
                        $ingredients = old('ingredients', $recipe->ingredients ?? []);
                        if (empty($ingredients)) {
                            $ingredients = [['quantity' => '', 'unit' => '', 'item' => '']];
                        }
                    @endphp
                    @foreach($ingredients as $i => $ing)
                        <div class="row g-2 mb-2 ingredient-row align-items-center">
                            <div class="col-6 col-sm-3 col-md-3">
                                <input type="text" name="ingredients[{{ $i }}][quantity]" class="form-control form-control-sm" value="{{ $ing['quantity'] ?? '' }}" placeholder="Qty">
                            </div>
                            <div class="col-6 col-sm-3 col-md-3">
                                <input type="text" name="ingredients[{{ $i }}][unit]" class="form-control form-control-sm" value="{{ $ing['unit'] ?? '' }}" placeholder="Unit">
                            </div>
                            <div class="col-10 col-sm-5 col-md-5">
                                <input type="text" name="ingredients[{{ $i }}][item]" class="form-control form-control-sm" value="{{ $ing['item'] ?? ($ing['name'] ?? '') }}" placeholder="Ingredient name" required>
                            </div>
                            <div class="col-2 col-sm-1 col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn w-100" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.data-card>

            {{-- Instructions Repeater --}}
            <x-admin.data-card class="mt-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-list-ol text-warning me-1"></i> Step-by-Step Directions
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-warning btn-pill" id="addStepBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Step
                    </button>
                </div>

                <div id="stepsContainer">
                    @php
                        $steps = old('instructions', $recipe->instructions ?? []);
                        if (empty($steps)) {
                            $steps = [['step' => 1, 'title' => '', 'text' => '']];
                        }
                    @endphp
                    @foreach($steps as $s => $step)
                        @php
                            $stepTitle = $step['title'] ?? null;
                            $stepText = $step['text'] ?? ($step['description'] ?? (is_string($step) ? $step : ''));
                        @endphp
                        <div class="step-row mb-3 p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-dark step-badge">Step {{ $s + 1 }}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-step-btn" title="Remove step">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="mb-2">
                                <input type="text" name="instructions[{{ $s }}][title]" class="form-control form-control-sm" value="{{ $stepTitle }}" placeholder="Step title (optional)">
                            </div>
                            <div>
                                <textarea name="instructions[{{ $s }}][text]" class="form-control form-control-sm" rows="2" placeholder="Detailed step instruction..." required>{{ $stepText }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.data-card>
        </div>

        {{-- Right Column: Metadata, Image & Product Association --}}
        <div class="col-lg-4">
            <x-admin.data-card>
                <h5 class="fw-bold mb-3 text-dark">Cooking Metadata</h5>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Prep Time (min)</label>
                        <input type="number" name="prep_time" class="form-control form-control-sm" value="{{ old('prep_time', $recipe->prep_time) }}" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Cook Time (min)</label>
                        <input type="number" name="cook_time" class="form-control form-control-sm" value="{{ old('cook_time', $recipe->cook_time) }}" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Servings</label>
                        <input type="number" name="servings" class="form-control form-control-sm" value="{{ old('servings', $recipe->servings) }}" min="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Calories (kcal)</label>
                        <input type="number" name="calories" class="form-control form-control-sm" value="{{ old('calories', $recipe->calories) }}" min="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Difficulty Level</label>
                    <select name="difficulty" class="form-select form-select-sm" required>
                        <option value="easy" {{ old('difficulty', $recipe->difficulty) === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', $recipe->difficulty) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty', $recipe->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Cuisine / Category</label>
                    <input type="text" name="cuisine" class="form-control form-control-sm" value="{{ old('cuisine', $recipe->cuisine) }}" placeholder="e.g. Italian, Beverage">
                </div>

                <hr class="my-3">

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $recipe->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="isActive">Published & Active</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" {{ old('is_featured', $recipe->is_featured) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="isFeatured">Featured Recipe</label>
                </div>
            </x-admin.data-card>

            {{-- Cover Image --}}
            <x-admin.data-card class="mt-4">
                <h5 class="fw-bold mb-3 text-dark">Cover Image</h5>

                @if($recipe->image_url)
                    <div class="mb-3 text-center">
                        <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="rounded shadow-sm" style="max-height: 120px; width: auto; object-fit: cover;">
                        <span class="d-block text-muted small mt-1">Current Image</span>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Upload New Image File</label>
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Or Image URL</label>
                    <input type="url" name="image_url" class="form-control form-control-sm" value="{{ old('image_url', $recipe->image_url) }}" placeholder="https://images.unsplash.com/...">
                </div>
            </x-admin.data-card>

            {{-- Link Products --}}
            <x-admin.data-card class="mt-4">
                <h5 class="fw-bold mb-3 text-dark">Eligible Store Products</h5>
                <p class="text-muted small mb-2">
                    Products that unlock this recipe and PDF upon successful payment:
                </p>

                @php
                    $selectedProductIds = old('product_ids', $recipe->products->pluck('id')->toArray());
                @endphp
                <div class="mb-3">
                    <select name="product_ids[]" id="productSelect" class="form-select form-select-sm" multiple size="8">
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ in_array($prod->id, $selectedProductIds) ? 'selected' : '' }}>
                                {{ $prod->name }} (ID: #{{ $prod->id }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">Hold ⌘ (Mac) or Ctrl (Windows) to select multiple products.</small>
                </div>
            </x-admin.data-card>

            {{-- Actions --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100 mb-2 py-2">
                    <i class="bi bi-check-circle me-1"></i> Update Recipe
                </button>
                <a href="{{ route('admin.recipes.index') }}" class="btn btn-outline-secondary w-100">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        var ingredientIndex = {{ count($ingredients) }};
        $('#addIngredientBtn').on('click', function() {
            var html = `
                <div class="row g-2 mb-2 ingredient-row align-items-center">
                    <div class="col-6 col-sm-3 col-md-3">
                        <input type="text" name="ingredients[${ingredientIndex}][quantity]" class="form-control form-control-sm" placeholder="Qty">
                    </div>
                    <div class="col-6 col-sm-3 col-md-3">
                        <input type="text" name="ingredients[${ingredientIndex}][unit]" class="form-control form-control-sm" placeholder="Unit">
                    </div>
                    <div class="col-10 col-sm-5 col-md-5">
                        <input type="text" name="ingredients[${ingredientIndex}][item]" class="form-control form-control-sm" placeholder="Ingredient name" required>
                    </div>
                    <div class="col-2 col-sm-1 col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn w-100" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>`;
            $('#ingredientsContainer').append(html);
            ingredientIndex++;
        });

        $(document).on('click', '.remove-row-btn', function() {
            if ($('.ingredient-row').length > 1) {
                $(this).closest('.ingredient-row').remove();
            } else {
                alert('At least one ingredient row is recommended.');
            }
        });

        var stepIndex = {{ count($steps) }};
        $('#addStepBtn').on('click', function() {
            var count = $('.step-row').length + 1;
            var html = `
                <div class="step-row mb-3 p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-dark step-badge">Step ${count}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-step-btn" title="Remove step">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="instructions[${stepIndex}][title]" class="form-control form-control-sm" placeholder="Step title">
                    </div>
                    <div>
                        <textarea name="instructions[${stepIndex}][text]" class="form-control form-control-sm" rows="2" placeholder="Detailed step instruction..." required></textarea>
                    </div>
                </div>`;
            $('#stepsContainer').append(html);
            stepIndex++;
        });

        $(document).on('click', '.remove-step-btn', function() {
            if ($('.step-row').length > 1) {
                $(this).closest('.step-row').remove();
                $('.step-row').each(function(i, el) {
                    $(el).find('.step-badge').text('Step ' + (i + 1));
                });
            } else {
                alert('At least one instruction step is required.');
            }
        });
    });
</script>
@endsection
