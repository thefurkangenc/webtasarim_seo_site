@props([
    // HasSeo kullanan model; yeni kayıtta null olabilir.
    'model' => null,
    'prefix' => 'seo',
    // Önizlemede meta alanları boşken hangi form alanına düşüleceği.
    'titleSource' => 'title',
    'descriptionSource' => 'excerpt',
    'slugSource' => 'slug',
    // Önizleme URL'inde başlığın önüne gelen yol: /blog/ornek-yazi
    'path' => '',
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $seo = $model?->seo;
    $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'site.com';
@endphp

@once
    @push('admin.scripts')
        <script type="module" src="{{ asset('admin/assets/js/core/seo-field.js') }}"></script>
    @endpush
@endonce

<div class="{{ $wrapper }}" data-seo
    data-seo-prefix="{{ $prefix }}"
    data-seo-title-source="{{ $titleSource }}"
    data-seo-description-source="{{ $descriptionSource }}"
    data-seo-slug-source="{{ $slugSource }}"
    data-seo-host="{{ $host }}"
    data-seo-path="{{ trim($path, '/') }}">

    {{-- Google sonuç önizlemesi --}}
    <div class="mb-[20px] md:mb-[25px] rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[17px]">
        <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[12px] uppercase tracking-[.5px]">
            Arama sonucu önizlemesi
        </span>

        <div class="max-w-[600px]">
            <div class="flex items-center gap-[6px] mb-[4px]">
                <span class="w-[20px] h-[20px] rounded-full bg-white dark:bg-[#0c1427] border border-gray-200 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <i class="material-symbols-outlined !text-[12px] text-gray-500 dark:text-gray-400">language</i>
                </span>
                <span data-seo-preview-url class="text-xs text-gray-600 dark:text-gray-400 truncate"></span>
            </div>

            <p data-seo-preview-title class="!mb-[3px] text-[18px] leading-[1.3] text-[#1a0dab] dark:text-[#8ab4f8] truncate"></p>
            <p data-seo-preview-description class="!mb-0 text-sm leading-[1.55] text-[#4d5156] dark:text-gray-400"></p>
        </div>
    </div>

    <div class="mb-[20px] md:mb-[25px]">
        <x-admin::form.label for="{{ $prefix }}-meta_title">Meta Başlık</x-admin::form.label>

        <input type="text" name="{{ $prefix }}[meta_title]" id="{{ $prefix }}-meta_title"
            data-seo-input="meta_title" data-seo-limit="60" maxlength="255"
            value="{{ old($prefix.'.meta_title', $seo?->meta_title) }}"
            placeholder="Boş bırakılırsa başlık kullanılır"
            class="h-[55px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">

        <div class="flex items-center justify-between gap-[10px] mt-[6px]">
            <x-admin::form.error :name="$prefix.'.meta_title'" />
            <span data-seo-counter="meta_title" class="text-xs shrink-0 text-gray-500 dark:text-gray-400"></span>
        </div>
    </div>

    <div class="mb-[20px] md:mb-[25px]">
        <x-admin::form.label for="{{ $prefix }}-meta_description">Meta Açıklama</x-admin::form.label>

        <textarea name="{{ $prefix }}[meta_description]" id="{{ $prefix }}-meta_description"
            data-seo-input="meta_description" data-seo-limit="160" rows="3" maxlength="500"
            placeholder="Boş bırakılırsa özet kullanılır"
            class="h-[100px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">{{ old($prefix.'.meta_description', $seo?->meta_description) }}</textarea>

        <div class="flex items-center justify-between gap-[10px] mt-[6px]">
            <x-admin::form.error :name="$prefix.'.meta_description'" />
            <span data-seo-counter="meta_description" class="text-xs shrink-0 text-gray-500 dark:text-gray-400"></span>
        </div>
    </div>

    <x-admin::form.input :name="$prefix.'.meta_keywords'" label="Meta Anahtar Kelimeler"
        :value="$seo?->meta_keywords" placeholder="virgülle ayırın: web tasarım, kurumsal site" />

    <x-admin::form.input :name="$prefix.'.canonical_url'" type="url" label="Canonical URL"
        :value="$seo?->canonical_url" placeholder="Aynı içerik başka bir adreste de varsa asıl adres" />

    <x-admin::form.image :name="$prefix.'.og_media_id'" label="Paylaşım Görseli"
        preset="seo.og" :media="$seo?->ogMedia"
        hint="Sosyal medyada paylaşıldığında görünen görsel. Boşsa kapak görseli kullanılır." />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
        <x-admin::form.switch :name="$prefix.'.robots_index'" label="Arama motorlarına açık"
            :checked="$seo?->robots_index ?? true"
            hint="Kapatılırsa noindex verilir; sayfa sonuçlarda çıkmaz." wrapper="" />

        <x-admin::form.switch :name="$prefix.'.robots_follow'" label="Bağlantılar takip edilsin"
            :checked="$seo?->robots_follow ?? true"
            hint="Kapatılırsa nofollow verilir." wrapper="" />
    </div>
</div>
