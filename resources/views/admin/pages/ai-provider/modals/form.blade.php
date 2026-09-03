{{-- AJAX modal gövdesi. Gönderim pages/ai-provider/index.js tarafından devralınır. --}}
@php
    // Bileşen özniteliği içinde dizi erişimi Blade'in öznitelik ayrıştırıcısını
    // bozuyor; ifadeler burada hazırlanıyor.
    $driverOptions = collect($drivers)->map(fn ($driver) => $driver['label'])->all();
    $keyPlaceholder = $provider?->api_key
        ? 'Kayıtlı anahtar korunuyor — değiştirmek için yeni anahtar yazın'
        : 'sk-...';
    $driverDefaults = collect($drivers)->map(fn ($driver) => [
        'base_url' => $driver['base_url'],
        'model' => $driver['model'],
        'requires_key' => $driver['requires_key'],
    ]);
@endphp
<form id="provider-form" data-id="{{ $provider?->id }}">
    <x-admin::form.select name="driver" label="Servis" required
        :value="$provider?->driver"
        :options="$driverOptions"
        data-provider-driver
        placeholder="Servis seçin" />

    {{-- Sürücü seçilince base_url/model alanlarını dolduracak varsayılanlar. --}}
    <script type="application/json" id="provider-driver-defaults">
        @json($driverDefaults)
    </script>

    <x-admin::form.input name="name" label="Ad" required :value="$provider?->name"
        placeholder="Örn. ChatGPT - Üretim" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
        <x-admin::form.input name="model" label="Model" required :value="$provider?->model"
            placeholder="gpt-4o-mini" wrapper="mb-[20px] md:mb-[25px]" />

        <x-admin::form.input name="base_url" label="API Adresi" required :value="$provider?->base_url"
            placeholder="https://api.openai.com/v1" wrapper="mb-[20px] md:mb-[25px]" />
    </div>

    <x-admin::form.input name="api_key" type="password" label="API Anahtarı"
        autocomplete="new-password"
        :placeholder="$keyPlaceholder"
        data-provider-key />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-[15px]">
        <x-admin::form.input name="temperature" type="number" label="Sıcaklık" required
            :value="$provider?->temperature ?? 0.7" step="0.1" min="0" max="2"
            wrapper="mb-[20px] md:mb-[25px]" />

        <x-admin::form.input name="max_tokens" type="number" label="Maks. Token" required
            :value="$provider?->max_tokens ?? 4000" min="100" max="32000"
            wrapper="mb-[20px] md:mb-[25px]" />

        <x-admin::form.input name="timeout" type="number" label="Zaman Aşımı (sn)" required
            :value="$provider?->timeout ?? 180" min="10" max="600"
            wrapper="mb-[20px] md:mb-[25px]" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-[15px] mb-[20px] md:mb-[25px]">
        <x-admin::form.switch name="json_mode" label="JSON modu"
            :checked="data_get($provider, 'options.json_mode', true)"
            hint="Modelden geçerli JSON istenir." wrapper="" />

        <x-admin::form.switch name="is_active" label="Aktif"
            :checked="$provider?->is_active ?? true" wrapper="" />

        <x-admin::form.switch name="is_default" label="Varsayılan"
            :checked="$provider?->is_default ?? false"
            hint="Şablonda servis seçilmezse bu kullanılır." wrapper="" />
    </div>

    @if ($provider)
        <div class="rounded-md border border-gray-100 dark:border-[#172036] p-[15px] mb-[20px] flex items-center justify-between gap-[12px] flex-wrap">
            <div>
                <p class="!mb-[3px] font-medium text-black dark:text-white">Bağlantı testi</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400" data-provider-test-result>
                    Kısa bir istek atarak anahtar ve modelin çalıştığını doğrular.
                </p>
            </div>
            <button type="button" data-provider-test
                class="shrink-0 inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[18px]">bolt</i> Test Et
            </button>
        </div>
    @endif

    <x-admin::form.actions :submit="$provider ? 'Güncelle' : 'Ekle'" />
</form>
