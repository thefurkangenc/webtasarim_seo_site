{{-- AJAX modal gövdesi. Gönderim pages/setting/social.js tarafından devralınır. --}}
<form id="social-link-form" data-id="{{ $link?->id }}">
    <x-admin::form.input name="name" label="Ad" required :value="$link?->name"
        placeholder="Örn. Instagram" />

    <x-admin::form.input name="url" label="Bağlantı" required :value="$link?->url"
        placeholder="https://instagram.com/hesap" />

    <x-admin::form.image name="icon_media_id" label="İkon" required
        preset="social.icon" :media="$link?->getFirstMedia('icon')" />

    <x-admin::form.actions :submit="$link ? 'Güncelle' : 'Ekle'" />
</form>
