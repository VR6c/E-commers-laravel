@extends('admin.layouts.admin')

@section('title', 'Create Recipe - Admin')

@section('content')

<x-admin.page-header
    :title="'Create New Recipe'"
    icon="bi bi-journal-plus"
    :subtitle="'Add a culinary recipe, define ingredients, steps, and associate store products'"
    :breadcrumbs="['Recipes' => route('admin.recipes.index'), 'Create' => '#']" />

<form action="{{ route('admin.recipes.store') }}" method="POST" enctype="multipart/form-data" id="recipeForm">
    @csrf

    <div class="row g-4">
        {{-- Left Column: Primary Details & Repeaters --}}
        <div class="col-lg-8">
            <x-admin.data-card>
                <h5 class="fw-bold mb-3 text-dark">Recipe Information</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Recipe Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Authentic Barista Espresso Macchiato" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Short Summary</label>
                    <textarea name="summary" class="form-control @error('summary') is-invalid @enderror" rows="2" placeholder="A brief teaser or description of the recipe for listings...">{{ old('summary') }}</textarea>
                    @error('summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Chef's Notes & Full Details</label>
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="4" placeholder="Pro-tips, equipment recommendations, origin story...">{{ old('content') }}</textarea>
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
                <p class="text-muted small">Specify quantities, units, and ingredients for the recipe card.</p>

                <div id="ingredientsContainer">
                    <div class="row g-2 mb-2 ingredient-row align-items-center">
                        <div class="col-6 col-sm-3 col-md-3">
                            <input type="text" name="ingredients[0][quantity]" class="form-control form-control-sm" placeholder="Qty (e.g. 2, 250)">
                        </div>
                        <div class="col-6 col-sm-3 col-md-3">
                            <input type="text" name="ingredients[0][unit]" class="form-control form-control-sm" placeholder="Unit (e.g. g, ml, tbsp)">
                        </div>
                        <div class="col-10 col-sm-5 col-md-5">
                            <input type="text" name="ingredients[0][item]" class="form-control form-control-sm" placeholder="Ingredient name (e.g. Espresso Beans)" required>
                        </div>
                        <div class="col-2 col-sm-1 col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn w-100" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
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
                <p class="text-muted small">Numbered steps shown to the user once unlocked.</p>

                <div id="stepsContainer">
                    <div class="step-row mb-3 p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-dark step-badge">Step 1</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-step-btn" title="Remove step">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="instructions[0][title]" class="form-control form-control-sm" placeholder="Step title (e.g. Grind & Dose)">
                        </div>
                        <div>
                            <textarea name="instructions[0][text]" class="form-control form-control-sm" rows="2" placeholder="Detailed step instruction..." required></textarea>
                        </div>
                    </div>
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
                        <input type="number" name="prep_time" class="form-control form-control-sm" value="{{ old('prep_time', 15) }}" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Cook Time (min)</label>
                        <input type="number" name="cook_time" class="form-control form-control-sm" value="{{ old('cook_time', 20) }}" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Servings</label>
                        <input type="number" name="servings" class="form-control form-control-sm" value="{{ old('servings', 2) }}" min="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Calories (kcal)</label>
                        <input type="number" name="calories" class="form-control form-control-sm" value="{{ old('calories') }}" min="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Difficulty Level</label>
                    <select name="difficulty" class="form-select form-select-sm" required>
                        <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Cuisine / Category</label>
                    <input type="text" name="cuisine" class="form-control form-control-sm" value="{{ old('cuisine') }}" placeholder="e.g. Italian, Beverage, Dessert">
                </div>

                <hr class="my-3">

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                    <label class="form-check-label fw-semibold small" for="isActive">Published & Active</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured">
                    <label class="form-check-label fw-semibold small" for="isFeatured">Featured Recipe</label>
                </div>
            </x-admin.data-card>

            {{-- Cover Image --}}
            <x-admin.data-card class="mt-4">
                <h5 class="fw-bold mb-3 text-dark">Cover Image</h5>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Upload Image File</label>
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Or Image URL</label>
                    <input type="url" name="image_url" class="form-control form-control-sm" value="{{ old('image_url') }}" placeholder="https://images.unsplash.com/...">
                </div>
            </x-admin.data-card>

            {{-- Link Products --}}
            <x-admin.data-card class="mt-4">
                <h5 class="fw-bold mb-3 text-dark">Eligible Products</h5>
                <p class="text-muted small mb-2">
                    When customers purchase any selected product and complete payment, this recipe and its PDF will unlock for them:
                </p>

                <div class="mb-3">
                    <select name="product_ids[]" id="productSelect" class="form-select form-select-sm" multiple size="8">
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ in_array($prod->id, old('product_ids', [])) ? 'selected' : '' }}>
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
                    <i class="bi bi-check-circle me-1"></i> Save & Publish Recipe
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
        var ingredientIndex = 1;
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

        var stepIndex = 1;
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
                // Re-number badges
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
