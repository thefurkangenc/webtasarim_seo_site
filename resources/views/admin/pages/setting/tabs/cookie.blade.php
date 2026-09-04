@php
    $enabled = filter_var($values['enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN);
    $cookiePage = route('cerez-politikasi');
@endphp

<form id="setting-form" action="{{ route('admin.setting.cookie.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">cookie</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Durum</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Açıkken analitik ve pazarlama kodları onaydan önce yüklenmez. Kapalıyken izleme kodları herkese basılır.
                </p>
            </div>
        </div>

        <div class="py-[1rem] px-[1rem] text-sm text-black dark:text-white bg-primary-50 border border-primary-200 dark:bg-[#15203c] dark:border-[#15203c] rounded-md mb-[20px]">
            Reddetmek kabul etmek kadar görünürdür. Zorunlu çerezler kilitlidir. Politika metni
            <a href="{{ route('admin.setting.edit', 'contents') }}" class="text-primary-500 hover:underline">İçerikler</a>
            sekmesindedir;
            <a href="{{ $cookiePage }}" target="_blank" rel="noopener noreferrer" class="text-primary-500 hover:underline">çerez politikası</a>
            sayfasında yayınlanır.
        </div>

        <x-admin::form.switch name="enabled" label="Çerez çubuğunu göster"
            :checked="$enabled"
            wrapper="mb-0" />
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">notes</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Çubuk metinleri</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Açıklamada {policy} yerine politika bağlantısı konur. Düğme metinleri eşit boyutta gösterilir.</p>
            </div>
        </div>

        <x-admin::form.input name="title" label="Başlık" required
            :value="$values['title'] ?? null" />

        <x-admin::form.textarea name="description" label="Açıklama" required
            :value="$values['description'] ?? null"
            rows="4" />

        <x-admin::form.input name="policy_label" label="Politika bağlantı metni" required
            :value="$values['policy_label'] ?? null" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]">
            <x-admin::form.input name="accept_label" label="Kabul düğmesi" required
                :value="$values['accept_label'] ?? null" wrapper="" />
            <x-admin::form.input name="reject_label" label="Reddet düğmesi" required
                :value="$values['reject_label'] ?? null" wrapper="" />
            <x-admin::form.input name="customize_label" label="Tercihler düğmesi" required
                :value="$values['customize_label'] ?? null" wrapper="" />
            <x-admin::form.input name="save_label" label="Kaydet düğmesi" required
                :value="$values['save_label'] ?? null" wrapper="" />
        </div>
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">category</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Kategoriler</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Google Tag Manager analitik onayına bağlıdır. Pikselleri GTM içine koyduysanız onlar da analitik kabulüne girer; İzleme Kodları’na yazılan Meta / TikTok / LinkedIn / Bing pazarlamaya bağlanır. Tawk.to işlevseldir. Search Console doğrulama meta etiketleri her zaman basılır.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-[20px] md:gap-[25px]">
            <div>
                <x-admin::form.input name="necessary_title" label="Zorunlu başlığı" required
                    :value="$values['necessary_title'] ?? null" />
                <x-admin::form.textarea name="necessary_description" label="Zorunlu açıklaması" required
                    :value="$values['necessary_description'] ?? null"
                    rows="3" wrapper="mb-0" />
            </div>
            <div>
                <x-admin::form.input name="functional_title" label="İşlevsel başlığı" required
                    :value="$values['functional_title'] ?? null" />
                <x-admin::form.textarea name="functional_description" label="İşlevsel açıklaması" required
                    :value="$values['functional_description'] ?? null"
                    rows="3" wrapper="mb-0" />
            </div>
            <div>
                <x-admin::form.input name="analytics_title" label="Analitik başlığı" required
                    :value="$values['analytics_title'] ?? null" />
                <x-admin::form.textarea name="analytics_description" label="Analitik açıklaması" required
                    :value="$values['analytics_description'] ?? null"
                    rows="3" wrapper="mb-0" />
            </div>
            <div>
                <x-admin::form.input name="marketing_title" label="Pazarlama başlığı" required
                    :value="$values['marketing_title'] ?? null" />
                <x-admin::form.textarea name="marketing_description" label="Pazarlama açıklaması" required
                    :value="$values['marketing_description'] ?? null"
                    rows="3" wrapper="mb-0" />
            </div>
        </div>
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">schedule</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Süre ve sürüm</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Sürümü artırmak, kayıtlı tercihleri geçersiz kılar; ziyaretçiden yeniden onay istenir.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]">
            <x-admin::form.input name="lifetime_days" type="number" label="Geçerlilik (gün)" required
                :value="$values['lifetime_days'] ?? 180"
                min="1" max="730" wrapper="" />
            <x-admin::form.input name="version" type="number" label="Politika sürümü" required
                :value="$values['version'] ?? 1"
                min="1" max="9999" wrapper="" />
        </div>
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
