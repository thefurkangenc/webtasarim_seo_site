{{-- AJAX modal gövdesi. --}}
<form id="popup-form" data-id="{{ $popup?->id }}">
    <x-admin::form.input name="title" help="popup.title" label="Yönetim adı" required
        :value="$popup?->title" placeholder="Örn. Bülten kaydı" />

    <x-admin::form.input name="heading" help="popup.heading" label="Başlık"
        :value="$popup?->heading" />

    <x-admin::form.textarea name="body" help="popup.body" label="Metin"
        :value="$popup?->body" rows="4" />

    <x-admin::form.image name="image_media_id" help="popup.image_media_id" label="Görsel"
        preset="popup.image" :media="$popup?->getFirstMedia('image')" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
        <x-admin::form.input name="button_label" help="popup.button_label" label="Buton yazısı"
            :value="$popup?->button_label" />
        <x-admin::form.input name="button_url" help="popup.button_url" label="Buton adresi"
            :value="$popup?->button_url" placeholder="https://..." />
    </div>

    <x-admin::form.input name="delay_seconds" help="popup.delay_seconds" label="Gecikme (saniye)"
        type="number" min="0" :max="config('notices.popup_delay_max')" :value="$popup?->delay_seconds ?? 2" />

    <x-admin::form.switch name="collect_email" help="popup.collect_email" label="E-posta topla (bülten formu)"
        :checked="$popup?->collect_email ?? false" />

    @include('admin.components.audience-fields', [
        'helpPrefix' => 'popup',
        'record' => $popup,
        'audiences' => $audiences,
        'pages' => $pages,
        'blogs' => $blogs,
        'services' => $services,
    ])

    <x-admin::form.actions :submit="$popup ? 'Güncelle' : 'Ekle'" />
</form>
