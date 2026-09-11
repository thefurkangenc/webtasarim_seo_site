{{--
    Ortak hedefleme alanları. Duyuru şeridi ve popup formları paylaşır.
    Seçili kayıtlar yalnızca audience=selected iken görünür (core/audience-form.js).
--}}
<x-admin::form.switch name="is_active" :help="$helpPrefix.'.is_active'" label="Yayında"
    :checked="$record?->is_active ?? true" />

<div class="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
    <x-admin::form.date name="starts_at" :help="$helpPrefix.'.starts_at'" label="Başlangıç"
        :value="$record?->starts_at" />
    <x-admin::form.date name="ends_at" :help="$helpPrefix.'.ends_at'" label="Bitiş"
        :value="$record?->ends_at" />
</div>

<x-admin::form.select name="audience" :help="$helpPrefix.'.audience'" label="Kim görsün" required
    :options="$audiences" :value="$record?->audience ?? 'all'" data-audience-select />

<div data-audience-targets class="{{ ($record?->audience ?? 'all') === 'selected' ? '' : 'hidden' }}">
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-[12px]">
        Yalnızca işaretlediğiniz sayfa, yazı ve hizmetlerde görünür. En az birini seçin.
    </p>
    <x-admin::form.select name="page_ids" :help="$helpPrefix.'.page_ids'" label="Sayfalar" multiple
        :options="$pages" :value="$record?->page_ids ?? []" placeholder="Sayfa seçin" />
    <x-admin::form.select name="blog_ids" :help="$helpPrefix.'.blog_ids'" label="Blog yazıları" multiple
        :options="$blogs" :value="$record?->blog_ids ?? []" placeholder="Yazı seçin" />
    <x-admin::form.select name="service_ids" :help="$helpPrefix.'.service_ids'" label="Hizmetler" multiple
        :options="$services" :value="$record?->service_ids ?? []" placeholder="Hizmet seçin" />
</div>
