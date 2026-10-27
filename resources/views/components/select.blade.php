@props(['name', 'label', 'options', 'selected' => null, 'required' => false, 'placeholder' => 'Pilih...'])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif
    </label>
    <select id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}
            @required($required)>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $nilai => $teks)
            <option value="{{ $nilai }}" @selected((string) old($name, $selected) === (string) $nilai)>{{ $teks }}</option>
        @endforeach
    </select>
    @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
