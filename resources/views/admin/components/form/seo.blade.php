@props([
    // HasSeo kullanan model; yeni kayıtta null olabilir.
    'model' => null,
    // `model` yoksa (örn. bir Eloquent kaydına bağlı olmayan site geneli
    // ayarlar) ham değerler burada verilir: meta_title/meta_description/meta_keywords.
    'values' => [],
    // `model` yoksa paylaşım görseli için doğrudan bir Media kaydı.
    'ogMedia' => null,
    // Boş bırakılırsa alan adları köşeli parantezsiz, düz yazılır (meta_title).
    // Model'e bağlı modüllerde iç içe göndermek için 'seo' kullanılır (seo[meta_title]).
    'prefix' => 'seo',
    // Kaynak alan boşken önizleme ve otomatik doldurma buraya düşer.
    // Meta alanı kullanıcı tarafından elle değiştirilmediği sürece kaynak
    // değiştikçe eşzamanlı güncellenir (bkz. core/seo-field.js).
    'titleSource' => 'title',
    'descriptionSource' => 'excerpt',
    'slugSource' => 'slug',
    // Kapak görseli alanının adı (örn. 'cover_media_id'). Verilmezse paylaşım
    // görseli otomatik doldurulmaz, elle seçilir.
    'imageSource' => null,
    // İçerik alanının (TinyMCE textarea) adı — canlı SEO analizi bunu okur.
    'contentSource' => 'content',
    // config/seo.php > min_words anahtarı: default / page / service.
    'analysisType' => 'default',
    // Odak kelime alanı + canlı skor paneli. Site geneli SEO ayarlarında
    // (bir içerik kaydına bağlı değil) kapatılır.
    'analysis' => true,
    // Önizleme URL'inde başlığın önüne gelen yol: /blog/ornek-yazi
    'path' => '',
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $seo = $model?->seo;
    $metaTitle = $model ? $seo?->meta_title : ($values['meta_title'] ?? null);
    $metaDescription = $model ? $seo?->meta_description : ($values['meta_description'] ?? null);
    $metaKeywords = $model ? $seo?->meta_keywords : ($values['meta_keywords'] ?? null);
    $ogMediaModel = $model ? $seo?->ogMedia : $ogMedia;

    // prefix boşsa alan adı düz kalır (meta_title), doluysa nokta notasyonuna
    // eklenir (seo.meta_title) — App\Support\Field bunu name="seo[meta_title]"'e çevirir.
    $key = fn (string $field) => $prefix ? "{$prefix}.{$field}" : $field;

    $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'site.com';
    $siteName = \App\Support\Settings::get('company.name') ?: $host;
    $logoId = \App\Support\Settings::get('company.logo_media_id');
    $favicon = $logoId ? \App\Models\Media\Media::query()->find($logoId)?->url('medium') : null;
    $focusKeyword = $model ? $seo?->focus_keyword : ($values['focus_keyword'] ?? null);
@endphp

@if ($analysis)
    @once
        @push('admin.scripts')
            <script type="module" src="{{ asset('admin/assets/js/core/seo-analyzer.js') }}"></script>
        @endpush
    @endonce
@endif

<div class="{{ $wrapper }}" data-seo
    data-seo-prefix="{{ $prefix }}"
    data-seo-title-source="{{ $titleSource }}"
    data-seo-description-source="{{ $descriptionSource }}"
    data-seo-slug-source="{{ $slugSource }}"
    data-seo-image-source="{{ $imageSource }}"
    data-seo-content-source="{{ $contentSource }}"
    data-seo-type="{{ $analysisType }}"
    data-seo-host="{{ $host }}"
    data-seo-sitename="{{ $siteName }}"
    data-seo-path="{{ trim($path, '/') }}"
    @if ($analysis) data-seo-rules="{{ json_encode(config('seo'), JSON_UNESCAPED_UNICODE) }}" @endif>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-[20px]">

        {{-- Sol: içerik alanları --}}
        <div>
            {{ $before ?? '' }}

            @if ($analysis)
                <div class="mb-[20px]">
                    <x-admin::form.label for="{{ $key('focus_keyword') }}" help="seo.focus_keyword">Odak Anahtar Kelime</x-admin::form.label>
                    <input type="text" name="{{ \App\Support\Field::name($key('focus_keyword')) }}" id="{{ \App\Support\Field::id($key('focus_keyword')) }}"
                        data-seo-input="focus_keyword" maxlength="120"
                        value="{{ old($key('focus_keyword'), $focusKeyword) }}"
                        placeholder="Örn. gaziantep web tasarım"
                        class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-[6px] block">
                        Sayfanın sıralanmasını istediğin ana ifade. Analiz buna göre yapılır.
                    </span>
                    <x-admin::form.error :name="$key('focus_keyword')" />
                </div>
            @endif

            <div class="mb-[20px]">
                <x-admin::form.label for="{{ $key('meta_title') }}" help="seo.meta_title">Meta Başlık</x-admin::form.label>

                <input type="text" name="{{ \App\Support\Field::name($key('meta_title')) }}" id="{{ \App\Support\Field::id($key('meta_title')) }}"
                    data-seo-input="meta_title" data-seo-limit="60" maxlength="255"
                    value="{{ old($key('meta_title'), $metaTitle) }}"
                    placeholder="Boş bırakılırsa başlık kullanılır"
                    class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">

                <div class="flex items-center justify-between gap-[10px] mt-[6px]">
                    <x-admin::form.error :name="$key('meta_title')" />
                    <span data-seo-counter="meta_title" class="text-xs shrink-0 text-gray-500 dark:text-gray-400"></span>
                </div>
            </div>

            <div class="mb-[20px]">
                <x-admin::form.label for="{{ $key('meta_description') }}" help="seo.meta_description">Meta Açıklama</x-admin::form.label>

                <textarea name="{{ \App\Support\Field::name($key('meta_description')) }}" id="{{ \App\Support\Field::id($key('meta_description')) }}"
                    data-seo-input="meta_description" data-seo-limit="160" rows="3" maxlength="500"
                    placeholder="Boş bırakılırsa özet kullanılır"
                    class="h-[90px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[12px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">{{ old($key('meta_description'), $metaDescription) }}</textarea>

                <div class="flex items-center justify-between gap-[10px] mt-[6px]">
                    <x-admin::form.error :name="$key('meta_description')" />
                    <span data-seo-counter="meta_description" class="text-xs shrink-0 text-gray-500 dark:text-gray-400"></span>
                </div>
            </div>

            <x-admin::form.input :name="$key('meta_keywords')" label="Meta Anahtar Kelimeler" help="seo.meta_keywords"
                :value="$metaKeywords" placeholder="virgülle ayırın: web tasarım, kurumsal site"
                wrapper="mb-0" />
        </div>

        {{-- Sağ: önizleme + paylaşım görseli --}}
        <div>
            <div class="mb-[20px]">
                <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[12px] uppercase tracking-[.5px]">
                    Google önizlemesi
                </span>
                <x-admin::form.google-preview :site-name="$siteName" :favicon="$favicon" />
            </div>

            <x-admin::form.image :name="$key('og_media_id')" label="Paylaşım Görseli" help="seo.og_media_id"
                preset="seo.og" :media="$ogMediaModel" wrapper="mb-0"
                hint="Sosyal medyada paylaşıldığında görünen görsel. Boşsa kapak görseli kullanılır." />
        </div>
    </div>

    {{-- Canlı SEO analizi — core/seo-analyzer.js doldurur --}}
    <div class="mt-[24px] pt-[20px] border-t border-gray-100 dark:border-[#172036] {{ $analysis ? '' : 'hidden' }}" data-seo-analysis>
        <div class="flex items-center gap-[16px] mb-[16px]">
            <div class="relative shrink-0 w-[64px] h-[64px]">
                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90 text-gray-100 dark:text-[#172036]">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3"></circle>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke-width="3" stroke-linecap="round"
                        data-seo-ring stroke-dasharray="0 100" class="transition-all"></circle>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-black dark:text-white" data-seo-score>–</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-black dark:text-white" data-seo-grade>Analiz için içerik girin</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400" data-seo-readability></span>
            </div>
            <button type="button" data-seo-toggle
                class="ltr:ml-auto rtl:mr-auto text-xs text-primary-500 hover:underline">tümünü göster</button>
        </div>

        <ul class="space-y-[7px] text-sm" data-seo-checks></ul>
    </div>
</div>
