@props([
    'siteName' => '',
    'favicon' => null,
])

@php
    $letter = mb_strtoupper(mb_substr($siteName !== '' ? $siteName : 'S', 0, 1));
@endphp

@once
    @push('admin.scripts')
        <script type="module" src="{{ asset('admin/assets/js/core/seo-field.js') }}"></script>
    @endpush
@endonce

{{-- Google SERP: arama çubuğu + sonuç sayısı + modern sonuç satırı. --}}
<div class="rounded-md border border-gray-100 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[20px]">
    <div class="flex items-center h-[46px] rounded-full border border-[#dfe1e5] dark:border-[#172036] px-[16px] gap-[12px]">
        <i class="material-symbols-outlined !text-[22px] text-[#9aa0a6] shrink-0">search</i>
        <span data-seo-preview-query class="flex-1 min-w-0 truncate text-sm text-[#202124] dark:text-white"></span>
        <i class="material-symbols-outlined !text-[22px] text-[#4285F4] shrink-0">mic</i>
    </div>

    <p class="!mb-0 mt-[12px] text-xs text-[#70757a] dark:text-gray-400">
        Yaklaşık 147.000 sonuç bulundu (0,48 saniye)
    </p>

    <div class="border-t border-gray-100 dark:border-[#172036] mt-[12px] pt-[16px]">
        <div class="flex items-start justify-between gap-[12px] mb-[6px]">
            <div class="flex items-center gap-[12px] min-w-0">
                <span class="w-[26px] h-[26px] rounded-full overflow-hidden bg-gray-50 dark:bg-[#15203c] border border-gray-200 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img data-seo-preview-favicon @if ($favicon) src="{{ $favicon }}" @endif alt=""
                        class="{{ $favicon ? '' : 'hidden' }} w-full h-full object-contain">
                    <span data-seo-preview-favicon-fallback
                        class="{{ $favicon ? 'hidden' : '' }} text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $letter }}</span>
                </span>
                <div class="min-w-0">
                    <span data-seo-preview-sitename
                        class="block text-base text-[#202124] dark:text-[#e8eaed] leading-[20px] truncate">{{ $siteName }}</span>
                    <span class="flex items-center min-w-0">
                        <span data-seo-preview-url
                            class="text-xs text-[#4d5156] dark:text-[#9aa0a6] leading-[18px] truncate"></span>
                        <i class="material-symbols-outlined !text-[16px] text-[#4d5156] dark:text-[#9aa0a6] shrink-0">arrow_drop_down</i>
                    </span>
                </div>
            </div>
            <i class="material-symbols-outlined !text-[20px] text-gray-400 dark:text-gray-500 shrink-0">more_vert</i>
        </div>

        <p data-seo-preview-title
            class="!mb-[4px] text-[20px] leading-[1.3] text-[#1a0dab] dark:text-[#8ab4f8] hover:underline cursor-pointer line-clamp-2"></p>
        <p data-seo-preview-description
            class="!mb-0 text-base leading-[1.58] text-[#4d5156] dark:text-[#bdc1c6] line-clamp-2"></p>
    </div>
</div>
