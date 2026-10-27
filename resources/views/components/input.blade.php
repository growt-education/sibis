@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif
    </label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
           @required($required)>
    @if ($help) <div class="form-text">{{ $help }}</div> @endif
    @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
