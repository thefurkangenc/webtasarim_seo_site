@php
    $enabled = filter_var($values['enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN);
    $secret = $values['bypass_secret'] ?? '';
@endphp

<form id="setting-form" action="{{ route('admin.setting.maintenance.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="settings-panel mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">construction</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Durum</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Yalnızca ön yüz kapanır. Yönetim paneli ve giriş yapmış kullanıcılar siteyi görmeye devam eder.
                </p>
            </div>
        </div>

        <div class="py-[1rem] px-[1rem] text-warning-500 bg-warning-50 border border-warning-200 dark:bg-[#15203c] dark:border-[#15203c] rounded-md mb-[20px] text-sm">
            Bu, Laravel’in <code class="text-xs">artisan down</code> komutu değildir; o komut paneli de kapatır. Bakım sayfası 503 döner.
        </div>

        <x-admin::form.switch name="enabled" help="maintenance.enabled" label="Bakım modunu aç"
            :checked="$enabled" />

        <a href="{{ route('maintenance.preview') }}" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center gap-[6px] text-sm text-primary-500 hover:underline">
            <i class="material-symbols-outlined !text-[18px]">open_in_new</i>
            Bakım sayfasını önizle
        </a>
    </div>

    <div class="settings-panel mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">article</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Sayfa içeriği</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Logo firma bilgilerinden alınır. Arama motorları bu sayfayı dizine eklemez.</p>
            </div>
        </div>

        <x-admin::form.input name="title" help="maintenance.title" label="Başlık" required
            :value="$values['title'] ?? null" />

        <x-admin::form.textarea name="message" help="maintenance.message" label="Mesaj" required
            :value="$values['message'] ?? null"
            rows="4"
            wrapper="mb-0" />
    </div>

    <div class="settings-panel">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">tune</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Gelişmiş</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Yeniden deneme süresi arama motorlarına Retry-After olarak gider. Anahtar, giriş yapmamış birine geçici erişim verir.
                </p>
            </div>
        </div>

        <x-admin::form.input name="retry_after" help="maintenance.retry_after" type="number" label="Yeniden deneme (dakika)"
            :value="$values['retry_after'] ?? null"
            min="1" max="10080"
            placeholder="Boş bırakılabilir" />

        <x-admin::form.input name="bypass_secret" help="maintenance.bypass_secret" label="Önizleme anahtarı"
            :value="$secret"
            placeholder="Örn. gecici-erisim"
            wrapper="mb-0" />
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">Harf, rakam, tire ve alt çizgi. Boşsa bu yol çalışmaz.</span>

        @if (filled($secret))
            <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400 mt-[12px] break-all">
                Geçici erişim:
                <a href="{{ route('maintenance.bypass', $secret) }}" target="_blank" rel="noopener noreferrer" class="text-primary-500 hover:underline">
                    {{ route('maintenance.bypass', $secret) }}
                </a>
            </p>
        @endif
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
