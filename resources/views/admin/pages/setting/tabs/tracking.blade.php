<div class="py-[1rem] px-[1rem] text-sm text-black dark:text-white bg-primary-50 border border-primary-200 dark:bg-[#15203c] dark:border-[#15203c] rounded-md mb-[20px] md:mb-[25px]">
    Yalnızca kimliği yazın, tam script yapıştırmayın. Listede olmayan bir servis için alttaki özel kod alanını kullanın.
</div>

<form id="setting-form" action="{{ route('admin.setting.tracking.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-[20px] md:gap-[25px]">
        <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
            <div class="flex items-center gap-[12px] mb-[20px]">
                <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img src="{{ asset('admin/assets/images/icons/tracking/google.svg') }}" alt="" class="w-[24px] h-[24px] object-contain">
                </span>
                <p class="!mb-0 font-medium text-black dark:text-white">Google</p>
            </div>

            <x-admin::form.input name="ga4_id" label="Google Analytics 4"
                :value="$values['ga4_id'] ?? null"
                placeholder="G-XXXXXXXXXX"
                maxlength="20"
                data-tracking-ga4
                @class(['opacity-50' => filled($values['gtm_id'] ?? null)]) />

            <div data-ga4-skip-notice
                class="{{ filled($values['gtm_id'] ?? null) ? '' : 'hidden' }} py-[1rem] px-[1rem] text-warning-500 bg-warning-50 border border-warning-200 dark:bg-[#15203c] dark:border-[#15203c] rounded-md mb-[20px] md:mb-[25px] text-sm">
                GTM dolu olduğu için GA4 sitede çalıştırılmaz. Analytics’i Tag Manager içinden ekleyin; sayım ikiye katlanmaz.
            </div>

            <x-admin::form.input name="gtm_id" label="Google Tag Manager"
                :value="$values['gtm_id'] ?? null"
                placeholder="GTM-XXXXXXX"
                maxlength="20"
                data-tracking-gtm />

            <x-admin::form.input name="google_site_verification" label="Search Console doğrulama"
                :value="$values['google_site_verification'] ?? null"
                placeholder="google-site-verification içeriği"
                wrapper="mb-0" />
        </div>

        <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
            <div class="flex items-center gap-[12px] mb-[20px]">
                <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img src="{{ asset('admin/assets/images/icons/tracking/microsoft.svg') }}" alt="" class="w-[24px] h-[24px] object-contain">
                </span>
                <p class="!mb-0 font-medium text-black dark:text-white">Microsoft</p>
            </div>

            <x-admin::form.input name="bing_uet_id" label="Bing UET"
                :value="$values['bing_uet_id'] ?? null"
                placeholder="12345678"
                maxlength="20" />

            <x-admin::form.input name="bing_verification" label="Bing Webmaster doğrulama"
                :value="$values['bing_verification'] ?? null"
                placeholder="msvalidate.01 içeriği"
                wrapper="mb-0" />
        </div>

        <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
            <div class="flex items-center gap-[12px] mb-[20px]">
                <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img src="{{ asset('admin/assets/images/icons/tracking/meta.svg') }}" alt="" class="w-[24px] h-[24px] object-contain">
                </span>
                <p class="!mb-0 font-medium text-black dark:text-white">Meta</p>
            </div>

            <x-admin::form.input name="meta_pixel_id" label="Meta Pixel"
                :value="$values['meta_pixel_id'] ?? null"
                placeholder="123456789012345"
                maxlength="20"
                wrapper="mb-0" />
        </div>

        <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
            <div class="flex items-center gap-[12px] mb-[20px]">
                <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img src="{{ asset('admin/assets/images/icons/tracking/yandex.svg') }}" alt="" class="w-[24px] h-[24px] object-contain">
                </span>
                <p class="!mb-0 font-medium text-black dark:text-white">Yandex</p>
            </div>

            <x-admin::form.input name="yandex_metrica_id" label="Yandex Metrica"
                :value="$values['yandex_metrica_id'] ?? null"
                placeholder="12345678"
                maxlength="20" />

            <x-admin::form.input name="yandex_verification" label="Yandex Webmaster doğrulama"
                :value="$values['yandex_verification'] ?? null"
                placeholder="yandex-verification içeriği"
                wrapper="mb-0" />
        </div>

        <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
            <div class="flex items-center gap-[12px] mb-[20px]">
                <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img src="{{ asset('admin/assets/images/icons/tracking/tiktok.svg') }}" alt="" class="w-[24px] h-[24px] object-contain">
                </span>
                <p class="!mb-0 font-medium text-black dark:text-white">TikTok</p>
            </div>

            <x-admin::form.input name="tiktok_pixel_id" label="TikTok Pixel"
                :value="$values['tiktok_pixel_id'] ?? null"
                placeholder="CXXXXXXXXXXXXXXX"
                maxlength="40"
                wrapper="mb-0" />
        </div>

        <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px]">
            <div class="flex items-center gap-[12px] mb-[20px]">
                <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                    <img src="{{ asset('admin/assets/images/icons/tracking/linkedin.svg') }}" alt="" class="w-[24px] h-[24px] object-contain">
                </span>
                <p class="!mb-0 font-medium text-black dark:text-white">LinkedIn</p>
            </div>

            <x-admin::form.input name="linkedin_partner_id" label="LinkedIn Insight"
                :value="$values['linkedin_partner_id'] ?? null"
                placeholder="123456"
                maxlength="20"
                wrapper="mb-0" />
        </div>
    </div>

    <div class="rounded-md border border-gray-100 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] p-[20px] md:p-[25px] mt-[20px] md:mt-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="w-[40px] h-[40px] rounded-md bg-white dark:bg-[#0c1427] border border-gray-100 dark:border-[#172036] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">code</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Özel kod</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Yukarıdaki servislerde olmayan bir izleme veya doğrulama kodunu buraya yapıştırın.
                </p>
            </div>
        </div>

        <x-admin::form.textarea name="head_scripts" label="Sayfa başına eklenecek kod"
            :value="$values['head_scripts'] ?? null"
            placeholder="head içine eklenecek script veya meta etiketleri"
            rows="4" />

        <x-admin::form.textarea name="body_scripts" label="Sayfa gövdesine eklenecek kod"
            :value="$values['body_scripts'] ?? null"
            placeholder="body açılışına eklenecek iframe veya script"
            rows="4"
            wrapper="mb-0" />
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
