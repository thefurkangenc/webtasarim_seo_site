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
    // Çoklu seçim: HTML name'i `alan[]` olur, boş placeholder seçeneği basılmaz
    // (Choices.js onu silinebilir bir etiket sanardı) ve `value` dizi beklenir.
    // Choices.js çoklu modu kendiliğinden devreye girer — core/select.js
    // `removeItemButton`'ı `select.multiple`'a göre açar.
    'multiple' => false,
])

@php
    $field = \App\Support\Field::name($name).($multiple ? '[]' : '');
    $id = \App\Support\Field::id($name);
    $selected = old($name, $value);
    // Çoklu modda karşılaştırma dizi üyeliği üzerinden yapılır; tekil modda
    // eskisi gibi tek değer karşılaştırması.
    $selectedKeys = $multiple
        ? collect($selected ?? [])->map(fn ($item) => (string) $item)->all()
        : [];
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
        @if ($multiple) multiple @endif
        @unless ($plain) data-choices @endunless
        {{ $attributes->merge(['class' => ($multiple ? 'min-h-[42px] py-[4px]' : 'h-[42px]').' rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all focus:border-primary-500']) }}>

        @if ($placeholder && ! $multiple)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            {{-- Seçenek ['label' => ..., 'icon' => '/path/icon.svg', 'depth' => 1] dizisi de
                 olabilir: 'icon' Choices.js'in soldaki ikonlu render'ı için, 'depth' ise
                 App\Support\Tree::options() ile üretilen ağaç görünümü için (örn. bölge
                 alt kategorileri). Render App\Support\Tree::render()'da — ham (bileşensiz)
                 filtre select'leri de aynı metodu kullanır, bkz. o dosyadaki doc yorumu. --}}
            @php
                $optionIcon = is_array($optionLabel) ? ($optionLabel['icon'] ?? null) : null;
                $optionDepth = is_array($optionLabel) ? (int) ($optionLabel['depth'] ?? 0) : 0;
                $optionText = is_array($optionLabel) ? $optionLabel['label'] : $optionLabel;
                $rendered = \App\Support\Tree::render($optionText, $optionDepth, $optionIcon);
            @endphp
            <option value="{{ $optionValue }}"
                @selected($multiple
                    ? in_array((string) $optionValue, $selectedKeys, true)
                    : (string) $selected === (string) $optionValue)
                @if ($rendered['customProperties']) data-custom-properties="{{ json_encode($rendered['customProperties']) }}" @endif>
                {{ $rendered['display'] }}
            </option>
        @endforeach
    </select>

    <x-admin::form.error :name="$name" />
</div>
