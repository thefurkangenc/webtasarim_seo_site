@props([
    // Gömülü adresi tutan alan.
    'name' => 'video_url',
    // Kütüphaneden seçilen/yüklenen dosyanın kimliğini tutan alan.
    'mediaName' => 'video_media_id',
    'label' => 'Video',
    'value' => null,
    'media' => null,
    'help' => null,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

{{-- İki kaynaktan biri: gömülü adres ya da yüklenmiş dosya. Sekme değiştirmek
     diğerini temizler (bkz. core/video-field.js) — böylece forma her zaman tek
     kaynak gider. Yalnızca video uzantıları kabul edilir; sınırı
     config/media.php > max_size_by_extension belirler. --}}

@php
    $urlField = \App\Support\Field::name($name);
    $mediaField = \App\Support\Field::name($mediaName);
    $url = old($name, $value);
    $videoAccepts = collect(config('media.accepts'))
        ->intersect(['mp4', 'webm'])
        ->map(fn ($extension) => ".{$extension}")
        ->implode(',');
    $limit = collect(config('media.max_size_by_extension', []))
        ->only(['mp4', 'webm'])
        ->max() ?? config('media.max_size');
@endphp

@once
    @push('admin.scripts')
        <script type="module" src="{{ asset('admin/assets/js/core/video-field.js') }}"></script>
    @endpush
@endonce

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :help="$help">{{ $label }}</x-admin::form.label>
    @endif

    <div data-video-field data-media-folder="">
        <div class="flex gap-[6px] mb-[12px]">
            <button type="button" data-video-tab="link"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs rounded-md border transition-all">
                <i class="material-symbols-outlined !text-[16px]">link</i> Bağlantı
            </button>
            <button type="button" data-video-tab="file"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs rounded-md border transition-all">
                <i class="material-symbols-outlined !text-[16px]">movie</i> Yüklenen dosya
            </button>
        </div>

        <div data-video-pane="link">
            <input type="url" name="{{ $urlField }}" data-video-url value="{{ $url }}"
                placeholder="https://www.youtube.com/watch?v=..."
                class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">

            <p data-video-status hidden class="!mb-0 mt-[8px] text-xs"></p>

            <span class="block mt-[8px] text-xs text-gray-500 dark:text-gray-400">
                YouTube ya da Vimeo adresini olduğu gibi yapıştırın — kısa adres, shorts ve
                zaman damgalı adres de çalışır. Sunucunuza yük bindirmez.
            </span>
        </div>

        <div data-video-pane="file" hidden>
            <input type="hidden" name="{{ $mediaField }}" data-video-media value="{{ old($mediaName, $media?->id) }}">
            <input type="file" data-video-file accept="{{ $videoAccepts }}" class="hidden">

            <div data-video-preview @if (! $media) hidden @endif>
                @if ($media)
                    <video src="{{ $media->url() }}" controls preload="metadata"
                        class="w-full max-h-[220px] rounded-md bg-black"></video>
                    <p class="!mb-0 mt-[8px] text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ $media->name }} · {{ $media->humanSize() }}
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-[8px] mt-[10px]">
                <button type="button" data-video-action="upload"
                    class="inline-flex items-center justify-center gap-[5px] py-[8px] px-[10px] text-xs text-white transition-all rounded-md bg-primary-500 border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                    <i class="material-symbols-outlined !text-[16px]">upload</i>
                    <span>{{ $media ? 'Değiştir' : 'Dosya Seç' }}</span>
                </button>
                <button type="button" data-video-action="library"
                    class="inline-flex items-center justify-center gap-[5px] py-[8px] px-[10px] text-xs text-white transition-all rounded-md bg-info-500 border border-info-500 hover:bg-info-400 hover:border-info-400">
                    <i class="material-symbols-outlined !text-[16px]">video_library</i> Kütüphaneden Seç
                </button>
            </div>

            {{-- 'flex' kullanılıyor: derlenmiş CSS'te 'inline-flex' 'hidden'den
                 SONRA geldiği için koşullu gizleme inline-flex ile çalışmaz. --}}
            <button type="button" data-video-action="clear" @if (! $media) hidden @endif
                class="mt-[8px] w-full flex items-center justify-center gap-[5px] py-[8px] px-[10px] text-xs text-danger-500 transition-all rounded-md border border-danger-200 dark:border-[#172036] hover:bg-danger-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[16px]">delete</i> Videoyu kaldır
            </button>

            <span class="block mt-[8px] text-xs text-gray-500 dark:text-gray-400">
                En fazla {{ round($limit / 1024, 1) }} MB. Büyük dosyalar ziyaretçinin
                sayfayı açma süresini uzatır — uzun videolar için bağlantı sekmesi daha iyidir.
            </span>
        </div>
    </div>

    <x-admin::form.error :name="$name" />
    <x-admin::form.error :name="$mediaName" />
</div>
