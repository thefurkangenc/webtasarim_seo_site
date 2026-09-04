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
        class="relative">

        <input type="hidden" name="{{ $field }}" data-media-input value="{{ old($name, $media?->id) }}">
        <input type="file" data-media-file accept="{{ $accepts }}" class="hidden">

        {{-- Önizleme / boş durum — aynı zamanda bırakma (drop) alanı. Dolu iken
             de dosya sürüklenip bırakılırsa mevcut görsel değiştirilir. --}}
        <div data-media-drop
            class="group relative w-full h-[190px] rounded-md overflow-hidden border-2 border-dashed border-gray-200 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] transition-all hover:border-primary-300 dark:hover:border-primary-500/50">

            <div data-media-preview class="{{ $media ? '' : 'hidden' }} absolute inset-0">
                <img data-media-image class="w-full h-full object-cover"
                    src="{{ $media?->url('medium') }}" data-original="{{ $media?->url() }}"
                    alt="{{ $media?->alt }}">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all pointer-events-none"></div>
            </div>

            <button type="button" data-media-action="select" data-media-empty
                class="{{ $media ? 'hidden' : '' }} absolute inset-0 w-full h-full flex flex-col items-center justify-center gap-[6px] text-gray-400 hover:text-primary-500 transition-all cursor-pointer">
                <i class="material-symbols-outlined !text-[36px]">cloud_upload</i>
                <span class="text-xs font-medium">Sürükleyip bırakın ya da tıklayın</span>
                @if ($size)
                    <span class="text-[11px] text-gray-400">Hedef boyut: {{ $size['width'] }}×{{ $size['height'] }} px</span>
                @endif
            </button>

            {{-- Sürükleme sırasında beliren vurgu katmanı. --}}
            <div data-media-dragover
                class="hidden absolute inset-0 z-[1] rounded-md bg-primary-500/10 border-2 border-primary-500 flex items-center justify-center pointer-events-none">
                <span class="flex items-center gap-[6px] text-primary-500 font-medium text-sm bg-white dark:bg-[#0c1427] py-[6px] px-[14px] rounded-md shadow-3xl">
                    <i class="material-symbols-outlined !text-[19px]">file_download</i> Bırakın
                </span>
            </div>

            {{-- Yükleme örtüsü --}}
            <div data-media-busy
                class="hidden absolute inset-0 z-[2] bg-white/80 dark:bg-[#0c1427]/80 flex items-center justify-center">
                <span class="flex items-center gap-[8px] text-primary-500 font-medium text-sm">
                    <i class="material-symbols-outlined animate-spin !text-[20px]">progress_activity</i> Yükleniyor...
                </span>
            </div>
        </div>

        {{-- Boşken hedef boyut zaten dropzone içinde yazıyor; tekrar etmesin. --}}
        <p data-media-info class="{{ $media ? '' : 'hidden' }} !mb-0 mt-[10px] text-xs text-gray-500 dark:text-gray-400 truncate">
            @if ($media)
                {{ $media->name }} · {{ $media->width }}×{{ $media->height }} · {{ $media->humanSize() }}
            @endif
        </p>

        {{-- Yalnızca görsel varken görünür; boşken tıklama/sürükleme zaten üstteki
             dropzone'dan yapılır. --}}
        <div data-media-actions class="{{ $media ? '' : 'hidden' }} flex items-center gap-[6px] flex-wrap mt-[10px]">
            <button type="button" data-media-action="select"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">upload</i> Değiştir
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
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-danger-500 transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-danger-100 dark:hover:bg-[#15203c] ltr:ml-auto rtl:mr-auto">
                <i class="material-symbols-outlined !text-[16px]">close</i> Kaldır
            </button>
        </div>
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
