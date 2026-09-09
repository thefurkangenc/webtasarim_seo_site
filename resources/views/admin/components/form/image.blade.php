@props([
    'name',
    'label' => null,
    // Tekil modda tek Media, çoklu modda Media koleksiyonu.
    'media' => null,
    'preset' => null,
    'folder' => null,
    'hint' => null,
    'required' => false,
    // Çoklu (galeri) mod: forma `name[]` id listesi ve `name_cover` kapak id'si
    // gider, davranışı core/media-gallery.js sürer. Çoklu modda nokta notasyonlu
    // alan adı kullanma — kapak input'unun adı `name_cover` olarak türetilir.
    'multiple' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    // Preset anahtarları nokta içerir ('blog.cover'), config() nokta notasyonunu
    // iç içe dizi sanacağı için doğrudan dizi erişimi kullanılıyor.
    $size = $preset ? (config('media.presets', [])[$preset] ?? null) : null;
    $accepts = collect(config('media.accepts'))->map(fn ($e) => ".{$e}")->implode(',');
    $field = \App\Support\Field::name($name);

    $items = $multiple ? collect($media ?? []) : collect();
    // Kapak, pivot üzerinde işaretlidir; işaret yoksa şeritteki ilk görsel.
    $coverId = $multiple
        ? ($items->firstWhere('pivot.is_cover', true)?->id ?? $items->first()?->id)
        : null;
    $first = $multiple ? $items->first() : $media;
    $showRecrop = ! $multiple && $media && $media->original_path && $size;
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    <div data-media-field
        @if ($multiple)
            data-media-multiple="1"
            data-media-name="{{ $field }}"
            data-media-cover="{{ $coverId }}"
            data-media-items="{{ json_encode($items->map->toPayload()->values()) }}"
        @endif
        data-media-preset="{{ $preset }}"
        data-media-width="{{ $size['width'] ?? '' }}"
        data-media-height="{{ $size['height'] ?? '' }}"
        data-media-label="{{ $size['label'] ?? '' }}"
        data-media-folder="{{ $folder }}"
        class="relative">

        @if ($multiple)
            {{-- Id listesi ve kapak seçimi JS tarafından doldurulur. --}}
            <div data-media-inputs class="hidden"></div>
            <input type="hidden" name="{{ $field }}_cover" data-media-cover-input value="{{ $coverId }}">
        @else
            <input type="hidden" name="{{ $field }}" data-media-input value="{{ old($name, $media?->id) }}">
        @endif

        <input type="file" data-media-file accept="{{ $accepts }}" @if ($multiple) multiple @endif class="hidden">

        {{-- Önizleme / boş durum — aynı zamanda bırakma (drop) alanı. Dolu iken
             de dosya sürüklenip bırakılırsa mevcut görsel değiştirilir
             (çoklu modda bırakılan dosyaların hepsi eklenir). --}}
        <div data-media-drop
            class="group relative w-full h-[190px] rounded-md overflow-hidden border-2 border-dashed border-gray-200 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] transition-all hover:border-primary-300 dark:hover:border-primary-500/50">

            <div data-media-preview class="{{ $first ? '' : 'hidden' }} absolute inset-0">
                <img data-media-image class="w-full h-full object-cover"
                    src="{{ $first?->url('medium') }}" data-original="{{ $first?->originalUrl() }}"
                    alt="{{ $first?->alt }}">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all pointer-events-none"></div>
            </div>

            <button type="button" data-media-action="select" data-media-empty
                class="{{ $first ? 'hidden' : '' }} absolute inset-0 w-full h-full flex flex-col items-center justify-center gap-[6px] text-gray-400 hover:text-primary-500 transition-all cursor-pointer">
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

            {{-- Kaldır: çerçevenin sağ üst köşesinde, sadece ikon, kırmızı.
                 'flex' kullanılıyor — 'inline-flex' compiled CSS'te 'hidden'den
                 SONRA geliyor, aynı önceliğe sahip iki kuralda kaynak sırası
                 kazanır; 'hidden inline-flex' birlikte kullanılırsa buton görünür
                 kalırdı (yaşanan hata buydu). 'flex' ise 'hidden'den önce gelir. --}}
            <button type="button" data-media-action="remove"
                class="{{ $first ? '' : 'hidden' }} absolute top-[8px] right-[8px] z-[3] w-[28px] h-[28px] flex items-center justify-center rounded-md bg-danger-500 text-white transition-all hover:bg-danger-600 shadow-3xl">
                <i class="material-symbols-outlined !text-[16px]">delete</i>
            </button>
        </div>

        {{-- Boşken hedef boyut zaten dropzone içinde yazıyor; tekrar etmesin. --}}
        <p data-media-info class="{{ $first ? '' : 'hidden' }} !mb-0 mt-[10px] text-xs text-gray-500 dark:text-gray-400 truncate">
            @if ($first)
                {{ $first->name }} · {{ $first->width }}×{{ $first->height }} · {{ $first->humanSize() }}
            @endif
        </p>

        {{-- Boşken 2, yeniden kırp mümkünken 3 eşit genişlikte sütun. --}}
        <div data-media-actions
            class="{{ $showRecrop ? 'grid-cols-3' : 'grid-cols-2' }} grid gap-[8px] mt-[10px]">
            <button type="button" data-media-action="select"
                class="inline-flex items-center justify-center gap-[5px] py-[8px] px-[10px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">upload</i>
                <span data-media-select-label>
                    @if ($multiple)
                        {{ $items->isNotEmpty() ? 'Görsel Ekle' : 'Dosya Seç' }}
                    @else
                        {{ $media ? 'Değiştir' : 'Dosya Seç' }}
                    @endif
                </span>
            </button>

            <button type="button" data-media-action="library"
                class="inline-flex items-center justify-center gap-[5px] py-[8px] px-[10px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">photo_library</i> Kütüphaneden Seç
            </button>

            <button type="button" data-media-action="recrop"
                class="{{ $showRecrop ? '' : 'hidden' }} flex items-center justify-center gap-[5px] py-[8px] px-[10px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">crop</i> Yeniden Kırp
            </button>
        </div>

        @if ($multiple)
            {{-- Küçük önizleme şeridi: çok sayıda görselde yatay kayar.
                 İçeriği core/media-gallery.js basar. --}}
            <div data-media-strip class="{{ $items->isEmpty() ? 'hidden' : '' }} mt-[10px]">
                <div data-media-thumbs class="flex gap-[8px] overflow-x-auto pb-[4px]"></div>
                <p class="!mb-0 mt-[6px] text-[11px] text-gray-500 dark:text-gray-400">
                    Görsele tıklayın büyük önizlemede açılsın; yıldız ile kapak görselini seçin.
                </p>
            </div>
        @endif
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
