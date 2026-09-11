@props([
    'name',
    'label' => null,
    // list<string> — mevcut değerler.
    'values' => [],
    'placeholder' => 'Yazıp Enter\'a basın...',
    'hint' => 'Enter ya da virgül ile ekleyin.',
    'help' => null,
    'max' => 60,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

{{-- Serbest metin listesi (teknolojiler, kapsam maddeleri…).
     <x-admin::form.tags> ile AYNI JS'i paylaşır (core/tag-input.js); tek fark
     öneri uç noktası verilmemesidir — burada seçilecek bir havuz yok, değerler
     kaydın kendi alanında yaşıyor. Etiket bileşeni yerine bu kullanılır çünkü
     etiketler `tags` tablosunda ortak bir sözlüktür, bu alan ise değildir. --}}

@php
    $field = \App\Support\Field::name($name);
    $items = collect(old($name, $values ?? []))->filter()->values();
@endphp

@once
    @push('admin.scripts')
        <script type="module" src="{{ asset('admin/assets/js/core/tag-input.js') }}"></script>
    @endpush
@endonce

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :help="$help">{{ $label }}</x-admin::form.label>
    @endif

    <div data-tag-input data-tag-name="{{ $field }}" data-tag-max="{{ $max }}"
        class="relative rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[8px] min-h-[42px] flex flex-wrap items-center gap-[6px] cursor-text transition-all focus-within:border-primary-500 text-sm">

        <div data-tag-chips class="contents">
            @foreach ($items as $item)
                <span data-tag-chip
                    class="inline-flex items-center gap-[5px] py-[5px] px-[10px] rounded-md text-xs bg-primary-50 dark:bg-[#15203c] text-primary-500 border border-primary-100 dark:border-[#172036]">
                    <input type="hidden" name="{{ $field }}[]" value="{{ $item }}">
                    <span>{{ $item }}</span>
                    <button type="button" data-tag-remove class="leading-none transition-all hover:text-danger-500">
                        <i class="ri-close-line"></i>
                    </button>
                </span>
            @endforeach
        </div>

        <input type="text" data-tag-field autocomplete="off" placeholder="{{ $placeholder }}"
            class="grow min-w-[140px] bg-transparent border-0 outline-0 text-black dark:text-white py-[5px] placeholder:text-gray-500 dark:placeholder:text-gray-400">
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
