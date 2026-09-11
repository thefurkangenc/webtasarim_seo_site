{{-- AJAX modal gövdesi. Gönderim pages/reference/index.js tarafından devralınır. --}}
<form id="reference-form" data-id="{{ $reference?->id }}" enctype="multipart/form-data">
    {{-- preset verilmiyor: logolar kendi oranında yüklensin, kırpma modalı açılmasın. --}}
    <x-admin::form.image name="logo_media_id" help="reference.logo_media_id" label="Firma Logosu" required
        :media="$reference?->getFirstMedia('logo')" />

    <x-admin::form.input name="name" help="reference.name" label="Firma Adı" required :value="$reference?->name"
        placeholder="Örn. BrightEdge Media" />

    <x-admin::form.input name="url" help="reference.url" label="Bağlantı" :value="$reference?->url"
        placeholder="https://ornek.com" />

    {{-- Sıra formdan girilmez: yeni kayıt otomatik en sona eklenir,
         sırayı değiştirmek için liste sayfasındaki "Sıralama Modu" kullanılır. --}}

    <x-admin::form.switch name="is_active" help="reference.is_active" label="Yayında"
        :checked="$reference?->is_active ?? true" />

    <x-admin::form.actions :submit="$reference ? 'Güncelle' : 'Ekle'" />
</form>
