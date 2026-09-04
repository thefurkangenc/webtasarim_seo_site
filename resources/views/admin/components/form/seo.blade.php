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
@endphp

<div class="{{ $wrapper }}" data-seo
    data-seo-prefix="{{ $prefix }}"
    data-seo-title-source="{{ $titleSource }}"
    data-seo-description-source="{{ $descriptionSource }}"
    data-seo-slug-source="{{ $slugSource }}"
    data-seo-image-source="{{ $imageSource }}"
    data-seo-host="{{ $host }}"
    data-seo-sitename="{{ $siteName }}"
    data-seo-path="{{ trim($path, '/') }}">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-[20px]">

        {{-- Sol: içerik alanları --}}
        <div>
            {{ $before ?? '' }}

            <div class="mb-[20px]">
                <x-admin::form.label for="{{ $key('meta_title') }}">Meta Başlık</x-admin::form.label>

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
                <x-admin::form.label for="{{ $key('meta_description') }}">Meta Açıklama</x-admin::form.label>

                <textarea name="{{ \App\Support\Field::name($key('meta_description')) }}" id="{{ \App\Support\Field::id($key('meta_description')) }}"
                    data-seo-input="meta_description" data-seo-limit="160" rows="3" maxlength="500"
                    placeholder="Boş bırakılırsa özet kullanılır"
                    class="h-[90px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[12px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">{{ old($key('meta_description'), $metaDescription) }}</textarea>

                <div class="flex items-center justify-between gap-[10px] mt-[6px]">
                    <x-admin::form.error :name="$key('meta_description')" />
                    <span data-seo-counter="meta_description" class="text-xs shrink-0 text-gray-500 dark:text-gray-400"></span>
                </div>
            </div>

            <x-admin::form.input :name="$key('meta_keywords')" label="Meta Anahtar Kelimeler"
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

            <x-admin::form.image :name="$key('og_media_id')" label="Paylaşım Görseli"
                preset="seo.og" :media="$ogMediaModel" wrapper="mb-0"
                hint="Sosyal medyada paylaşıldığında görünen görsel. Boşsa kapak görseli kullanılır." />
        </div>
    </div>
</div>
