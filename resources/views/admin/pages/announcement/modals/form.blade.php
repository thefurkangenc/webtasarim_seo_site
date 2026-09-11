{{-- AJAX modal gövdesi. --}}
<form id="announcement-form" data-id="{{ $announcement?->id }}">
    <x-admin::form.input name="title" help="announcement.title" label="Yönetim adı" required
        :value="$announcement?->title" placeholder="Örn. Yaz kampanyası" />

    <x-admin::form.textarea name="message" help="announcement.message" label="Şeritte görünen metin" required
        :value="$announcement?->message" rows="3" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-[16px]">
        <x-admin::form.input name="button_label" help="announcement.button_label" label="Buton yazısı"
            :value="$announcement?->button_label" placeholder="Örn. İncele" />
        <x-admin::form.input name="button_url" help="announcement.button_url" label="Buton adresi"
            :value="$announcement?->button_url" placeholder="https://..." />
    </div>

    <x-admin::form.select name="tone" help="announcement.tone" label="Renk" required
        :options="$tones" :value="$announcement?->tone ?? 'primary'" />

    @include('admin.components.audience-fields', [
        'helpPrefix' => 'announcement',
        'record' => $announcement,
        'audiences' => $audiences,
        'pages' => $pages,
        'blogs' => $blogs,
        'services' => $services,
    ])

    <x-admin::form.actions :submit="$announcement ? 'Güncelle' : 'Ekle'" />
</form>
