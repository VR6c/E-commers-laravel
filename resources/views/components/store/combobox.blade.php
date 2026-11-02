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
        <label for="{{ $id }}-trigger" class="xsf-field__label">
            {{ $label }} @if ($required)<span class="text-danger" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ old($name, $value) }}" @if($required) required @endif />

    <button type="button" class="xsf-combobox__trigger" id="{{ $id }}-trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="{{ $id }}-panel" @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
        <span class="xsf-combobox__label {{ empty($value) ? 'xsf-combobox__label--placeholder' : '' }}" id="{{ $id }}-label">
            {{ $value ?: $placeholder }}
        </span>
    </button>
    @error($name)
        <p class="xsf-field__error" id="{{ $id }}-error" role="alert">{{ $message }}</p>
    @enderror
</div>
