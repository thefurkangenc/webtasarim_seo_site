{{-- AJAX modal gövdesi. Gönderim pages/setting/integration.js tarafından devralınır. --}}
<form id="integration-form" data-key="{{ $key }}">
    @include('admin.pages.setting.modals.integrations.'.$key)

    <x-admin::form.actions :submit="$enabled ? 'Kaydet' : 'Kaydet ve aktifleştir'" />
</form>
