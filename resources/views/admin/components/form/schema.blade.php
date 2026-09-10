@props([
    // HasSeo kullanan model; yeni kayıtta null olabilir.
    'model' => null,
    // Alan adları bu prefix altında iç içe gider (seo tablosuna yazılır).
    'prefix' => 'seo',
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $seo = $model?->seo;
    $key = fn (string $field) => $prefix ? "{$prefix}.{$field}" : $field;
    $schemaJson = is_array($seo?->schema_json)
        ? json_encode($seo->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : null;
@endphp

<div class="{{ $wrapper }}">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-[18px] leading-relaxed">
        Bu kayıt için ön yüzde otomatik <strong>JSON-LD</strong> işaretlemesi üretilir
        (sayfa türüne göre WebPage / Service / BlogPosting, varsa SSS ve site geneli
        Organization). Aşağıdakiler yalnızca <strong>istisnai durumlar</strong> içindir —
        çoğu kayıtta boş kalır.
    </p>

    <x-admin::form.input :name="$key('schema_type')" label="Ana düğüm türünü değiştir" help="schema.record_type"
        :value="$seo?->schema_type"
        placeholder="Boş bırakın (otomatik). Örn: Service, Product, Event, HowTo"
        wrapper="mb-[16px]" />
    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-[10px] mb-[18px]">
        Yalnızca bu sayfanın ana Schema.org türünü değiştirir; geçerli bir
        <a href="https://schema.org/docs/full.html" target="_blank" rel="noopener" class="text-primary-500 hover:underline">schema.org türü</a> yazın.
    </p>

    <div class="mb-[16px]">
        <x-admin::form.label :for="\App\Support\Field::id($key('schema_json'))" help="schema.record_json">Ek JSON-LD (gelişmiş)</x-admin::form.label>
        <textarea name="{{ \App\Support\Field::name($key('schema_json')) }}"
            id="{{ \App\Support\Field::id($key('schema_json')) }}"
            rows="8" spellcheck="false"
            placeholder='{{ '{ "@type": "HowTo", "name": "..." }' }}'
            class="rounded-md text-xs font-mono leading-relaxed text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[12px] block w-full outline-0 transition-all focus:border-primary-500 min-h-[160px]">{{ old($key('schema_json'), $schemaJson) }}</textarea>
        <x-admin::form.error :name="$key('schema_json')" />
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-[6px]">
            Geçerli JSON — tek bir nesne ya da nesne dizisi. Üretilen <code>@graph</code>'a
            olduğu gibi eklenir.
        </p>
    </div>

    <x-admin::form.switch :name="$key('schema_override')" label="Otomatik üretimi kapat" help="schema.record_override"
        :checked="(bool) ($seo?->schema_override ?? false)"
        hint="Açıksa bu sayfa için WebPage / Service / BlogPosting / SSS / breadcrumb düğümleri üretilmez; yalnızca site geneli Organization + WebSite ve yukarıdaki JSON basılır."
        wrapper="mb-0" />

    @if ($model?->getKey())
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-[16px]">
            Üretilen çıktıyı görmek için:
            <a href="{{ route('admin.schema.index') }}" target="_blank" rel="noopener" class="text-primary-500 hover:underline">Schema.org doğrulama ekranı</a>.
        </p>
    @endif
</div>
