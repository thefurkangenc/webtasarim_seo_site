@props([
    'name',
    'label' => null,
    'media' => null,
    'preset' => null,
    'folder' => null,
    'hint' => null,
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    // Preset anahtarları nokta içerir ('blog.cover'), config() nokta notasyonunu
    // iç içe dizi sanacağı için doğrudan dizi erişimi kullanılıyor.
    $size = $preset ? (config('media.presets', [])[$preset] ?? null) : null;
    $accepts = collect(config('media.accepts'))->map(fn ($e) => ".{$e}")->implode(',');
    $field = \App\Support\Field::name($name);
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    <div data-media-field
        data-media-preset="{{ $preset }}"
        data-media-width="{{ $size['width'] ?? '' }}"
        data-media-height="{{ $size['height'] ?? '' }}"
        data-media-label="{{ $size['label'] ?? '' }}"
        data-media-folder="{{ $folder }}"
        class="relative border border-gray-200 dark:border-[#172036] rounded-md p-[12px]">

        <input type="hidden" name="{{ $field }}" data-media-input value="{{ old($name, $media?->id) }}">
        <input type="file" data-media-file accept="{{ $accepts }}" class="hidden">

        {{-- Önizleme — üstte, geniş; kırpım sonucu net görülsün diye. --}}
        <div data-media-preview
            class="{{ $media ? '' : 'hidden' }} w-full h-[190px] rounded-md overflow-hidden border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] mb-[12px]">
            <img data-media-image class="w-full h-full object-cover"
                src="{{ $media?->url('thumb') }}" data-original="{{ $media?->url() }}"
                alt="{{ $media?->alt }}">
        </div>

        {{-- Boş durum --}}
        <div data-media-empty
            class="{{ $media ? 'hidden' : '' }} w-full h-[190px] rounded-md border border-dashed border-gray-200 dark:border-[#172036] flex items-center justify-center text-gray-400 mb-[12px]">
            <i class="material-symbols-outlined !text-[36px]">image</i>
        </div>

        <p data-media-info class="!mb-[10px] text-xs text-gray-500 dark:text-gray-400 truncate">
            @if ($media)
                {{ $media->name }} · {{ $media->width }}×{{ $media->height }} · {{ $media->humanSize() }}
            @elseif ($size)
                Hedef boyut: {{ $size['width'] }}×{{ $size['height'] }} px
            @else
                Henüz görsel seçilmedi
            @endif
        </p>

        <div class="flex items-center gap-[6px] flex-wrap">
            <button type="button" data-media-action="select"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                <i class="material-symbols-outlined !text-[16px]">upload</i> Dosya Seç
            </button>

            <button type="button" data-media-action="library"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">photo_library</i> Kütüphaneden Seç
            </button>

            <button type="button" data-media-action="recrop"
                class="{{ $media?->original_path && $size ? '' : 'hidden' }} inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">crop</i> Yeniden Kırp
            </button>

            <button type="button" data-media-action="remove"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-danger-500 transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-danger-100 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">close</i> Kaldır
            </button>
        </div>

        {{-- Yükleme örtüsü --}}
        <div data-media-busy
            class="hidden absolute inset-0 z-[2] rounded-md bg-white/80 dark:bg-[#0c1427]/80 flex items-center justify-center">
            <span class="flex items-center gap-[8px] text-primary-500 font-medium text-sm">
                <i class="material-symbols-outlined animate-spin !text-[20px]">progress_activity</i> Yükleniyor...
            </span>
        </div>
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
