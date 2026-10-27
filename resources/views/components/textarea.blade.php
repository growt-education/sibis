@props(['name', 'label', 'value' => null, 'required' => false, 'rows' => 4])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif
    </label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
              @required($required)>{{ old($name, $value) }}</textarea>
    @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
