@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Seçiniz',
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
    // true verilirse Choices.js uygulanmaz, tarayıcının native select'i kalır.
    'plain' => false,
])

@php
    $field = \App\Support\Field::name($name);
    $id = \App\Support\Field::id($name);
    $selected = old($name, $value);
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$id" :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    {{-- core/select.js bunu Choices.js ile değiştirir; native select DOM'da
         kalır, form gönderimi ve doğrulama hataları etkilenmez. --}}
    <select
        name="{{ $field }}"
        id="{{ $id }}"
        @if ($required) required @endif
        @unless ($plain) data-choices @endunless
        {{ $attributes->merge(['class' => 'h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all focus:border-primary-500']) }}>

        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            {{-- Seçenek ['label' => ..., 'icon' => '/path/icon.svg'] dizisi de olabilir —
                 Choices.js'in soldaki ikonlu render'ı için (core/select.js `withIcons`). --}}
            @php
                $optionIcon = is_array($optionLabel) ? ($optionLabel['icon'] ?? null) : null;
                $optionText = is_array($optionLabel) ? $optionLabel['label'] : $optionLabel;
            @endphp
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)
                @if ($optionIcon) data-custom-properties="{{ json_encode(['icon' => $optionIcon]) }}" @endif>
                {{ $optionText }}
            </option>
        @endforeach
    </select>

    <x-admin::form.error :name="$name" />
</div>
