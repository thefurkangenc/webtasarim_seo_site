@php
    $connected = ! empty($values['has_service_account']);
@endphp

<form id="analytics-form" action="{{ route('admin.setting.analytics.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px] md:mb-[25px] leading-relaxed">
        Panelin <a href="{{ route('admin.analytics.index') }}" class="text-primary-500 hover:underline">Analitik</a>
        ekranı, Google Analytics 4 verisini bir <strong>service account</strong> ile çeker (harici paket yok, JWT
        kendimiz imzalıyoruz). Ön yüzdeki GA4 izleme kodu ayrı — o
        <a href="{{ route('admin.setting.edit', 'tracking') }}" class="text-primary-500 hover:underline">İzleme Kodları</a>'nda.
    </p>

    @if ($connected)
        <div class="flex items-start gap-[10px] p-[14px] rounded-md bg-success-50 dark:bg-[#15203c] border border-success-200 dark:border-[#172036] mb-[20px] md:mb-[25px]">
            <i class="material-symbols-outlined !text-[20px] text-success-600">check_circle</i>
            <div class="text-sm">
                <span class="block font-medium text-black dark:text-white">Bağlı</span>
                <span class="block text-gray-500 dark:text-gray-400 break-all">{{ $values['client_email'] ?? '—' }}</span>
                @if (! empty($values['property_id']))
                    <span class="block text-gray-500 dark:text-gray-400">Property ID: {{ $values['property_id'] }}</span>
                @endif
            </div>
        </div>
    @endif

    <x-admin::form.input name="property_id" label="GA4 Property ID" required
        :value="$values['property_id'] ?? null"
        placeholder="Örn. 493819123"
        inputmode="numeric" />
    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-[14px] mb-[20px] md:mb-[25px]">
        Google Analytics &rsaquo; Yönetici &rsaquo; Mülk Ayarları'nda yazan sayısal kimlik.
        <strong>G- ile başlayan ölçüm kimliği değildir.</strong>
    </p>

    <div class="mb-[20px] md:mb-[25px]">
        <label class="mb-[10px] text-black dark:text-white font-medium block">
            Service account JSON {{ $connected ? '(değiştirmek için yeni dosya yükleyin)' : '' }}
        </label>
        <input type="file" name="service_account" accept="application/json,.json"
            class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-[14px] file:py-[9px] file:px-[16px] file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-500 file:text-white hover:file:bg-primary-400 file:cursor-pointer border border-gray-200 dark:border-[#172036] rounded-md p-[8px] bg-white dark:bg-[#0c1427]">
        <span class="text-danger-500 text-xs mt-[6px] block" data-error="service_account"></span>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-[6px]">
            Dosya sunucuda <strong>şifreli</strong> saklanır, panele bir daha gösterilmez.
        </p>
    </div>

    <details class="mb-[20px] md:mb-[25px] rounded-md border border-gray-100 dark:border-[#172036] p-[14px] text-sm">
        <summary class="cursor-pointer font-medium text-black dark:text-white">Kurulum adımları</summary>
        <ol class="list-decimal ltr:pl-[18px] rtl:pr-[18px] mt-[10px] space-y-[6px] text-gray-600 dark:text-gray-300">
            <li>Google Cloud Console &rsaquo; APIs &amp; Services &rsaquo; <strong>Google Analytics Data API</strong>'yi etkinleştirin.</li>
            <li>IAM &amp; Admin &rsaquo; Service Accounts &rsaquo; yeni bir hesap oluşturun, <strong>JSON anahtar</strong> indirin.</li>
            <li>Google Analytics &rsaquo; Yönetici &rsaquo; <strong>Mülk Erişim Yönetimi</strong>'nde bu hesabın e-postasını <strong>Görüntüleyici (Viewer)</strong> olarak ekleyin.</li>
            <li>İndirdiğiniz JSON dosyasını yukarıya yükleyin, property ID'yi girin ve kaydedin.</li>
        </ol>
    </details>

    <div class="trezo-card-footer flex flex-wrap items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="button" id="analytics-test" {{ $connected ? '' : 'disabled' }}
            class="inline-flex items-center gap-[6px] py-[10px] px-[20px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c] disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="material-symbols-outlined !text-[18px]">wifi_tethering</i>
            Bağlantıyı test et
        </button>
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
