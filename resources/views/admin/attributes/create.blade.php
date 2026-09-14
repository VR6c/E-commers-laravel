@extends('admin.layouts.admin')
@section('title', 'Create Attribute — Admin')

@section('content')

<x-admin.page-header
    :title="'Create Attribute'"
    :breadcrumbs="['Attributes' => route('admin.attributes.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Attributes
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.attributes.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Attribute Details'" :icon="'bi bi-sliders'">
                <!-- Attribute Name -->
                <div class="mb-4">
                    <label for="name" class="form-label fw-semibold text-dark">{{ 'Attribute Name' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="e.g. Color, Size, Material, Storage Capacity"
                           required
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <!-- Attribute Values -->
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <label class="form-label fw-semibold text-dark mb-0">{{ 'Attribute Values' }}</label>
                            <small class="text-muted d-block">Define available options for this attribute</small>
                        </div>
                        <button type="button" id="add-value" class="btn btn-sm btn-outline-primary shadow-xs">
                            <i class="bi bi-plus-circle me-1"></i> Add Value
                        </button>
                    </div>

                    <div id="attribute-values-container">
                        @php $oldValues = old('values', ['']); @endphp
                        @foreach ($oldValues as $index => $val)
                        <div class="mb-3 value-group position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-tag"></i></span>
                                <input type="text"
                                       name="values[]"
                                       class="form-control @error('values.' . $index) is-invalid @enderror"
                                       value="{{ $val }}"
                                       placeholder="Enter value {{ $index + 1 }}">
                                <button type="button"
                                        class="btn btn-outline-danger remove-value px-3"
                                        title="{{ 'Remove Value' }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                            @error('values.' . $index)
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        @endforeach
                    </div>
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Publish & Action'" :icon="'bi bi-check-circle'">
                <div class="p-3 bg-light rounded mb-4" style="border: 1px solid var(--border-subtle);">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle text-primary fs-5 mt-n1"></i>
                        <p class="text-muted small mb-0">
                            Attributes define product variations (e.g. Size, Color). Once created, you can assign them to variant-enabled products.
                        </p>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Save Attribute' }}
                    </button>
                    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>
@endsection

@section('js')
<script>
document.addEventListener("DOMContentLoaded", function () {
    function updatePlaceholders() {
        document.querySelectorAll("#attribute-values-container .value-group input").forEach((input, i) => {
            input.placeholder = "Enter value " + (i + 1);
        });
    }

    document.getElementById("add-value").addEventListener("click", function () {
        var container = document.getElementById("attribute-values-container");
        var count = container.querySelectorAll('.value-group').length + 1;
        var valueGroup = document.createElement("div");
        valueGroup.classList.add("mb-3", "value-group");

        valueGroup.innerHTML = `
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-tag"></i></span>
                <input type="text" name="values[]" class="form-control" placeholder="Enter value ` + count + `">
                <button type="button" class="btn btn-outline-danger remove-value px-3" title="Remove Value">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        `;

        container.appendChild(valueGroup);
        updatePlaceholders();
    });

    document.addEventListener("click", function (e) {
        if (e.target.closest(".remove-value")) {
            e.target.closest(".value-group").remove();
            updatePlaceholders();
        }
    });

    updatePlaceholders();
});
</script>
@endsection
