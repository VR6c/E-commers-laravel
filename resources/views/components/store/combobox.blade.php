@props([
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => 'Select an option...',
    'searchPlaceholder' => 'Search...',
    'required' => false,
    'value' => null,
    'options' => [], // array of ['code' => '...', 'name' => '...'] or strings
])

@php
    $id = $id ?? $name;
@endphp

<div class="xsf-combobox" id="{{ $id }}-combobox">
    @if ($label)
        <label for="{{ $id }}" class="form-label fw-semibold text-dark small mb-1">
            {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ old($name, $value) }}" @if($required) required @endif />

    <button type="button" class="xsf-combobox__trigger w-100 d-flex justify-content-between align-items-center" id="{{ $id }}-trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="xsf-combobox__label {{ empty($value) ? 'xsf-combobox__label--placeholder' : '' }}" id="{{ $id }}-label">
            {{ $value ?: $placeholder }}
        </span>
    </button>
</div>
