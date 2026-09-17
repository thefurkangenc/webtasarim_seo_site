@php
    $enabled = filter_var($values['enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN);
    $driverOptions = (array) config('captcha.labels', []);
    $forms = [
        'form_contact' => ['label' => 'İletişim formu', 'help' => 'captcha.form_contact'],
        'form_quote' => ['label' => 'Teklif formu', 'help' => 'captcha.form_quote'],
        'form_newsletter' => ['label' => 'Bülten (footer ve açılır pencere)', 'help' => 'captcha.form_newsletter'],
        'form_login' => ['label' => 'Panel giriş ekranı', 'help' => 'captcha.form_login'],
    ];
@endphp

<form id="setting-form" action="{{ route('admin.setting.captcha.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="settings-panel mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">verified_user</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Durum</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Kapatılınca formlardaki doğrulama kutusu hiç basılmaz ve gönderimler dokunulmadan çalışmaya devam eder.
                </p>
            </div>
        </div>

        <div class="py-[1rem] px-[1rem] text-primary-500 bg-primary-50 border border-primary-100 dark:bg-[#15203c] dark:border-[#15203c] rounded-md mb-[20px] text-sm">
            Doğrulama tamamen bu sunucuda çalışır: Google reCAPTCHA gibi bir servise hesap açmak ya da anahtar girmek gerekmez.
        </div>

        <x-admin::form.switch name="enabled" help="captcha.enabled" label="Güvenlik doğrulamasını aç"
            :checked="$enabled" />

        <x-admin::form.select name="driver" help="captcha.driver" label="Doğrulama türü"
            :options="$driverOptions" :value="$values['driver'] ?? 'puzzle'"
            wrapper="mb-0" />
    </div>

    <div class="settings-panel mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">checklist</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Hangi formlarda</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Kapalı bir form doğrulama istemez; kutu o formda görünmez.
                </p>
            </div>
        </div>

        @foreach ($forms as $key => $form)
            <x-admin::form.switch :name="$key" :help="$form['help']" :label="$form['label']"
                :checked="filter_var($values[$key] ?? '1', FILTER_VALIDATE_BOOLEAN)" />
        @endforeach
    </div>

    <div class="settings-panel">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">tune</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Zorluk</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                    Parçanın yerine oturmuş sayılması için tanınan sapma payı. Büyütmek bulmacayı kolaylaştırır.
                </p>
            </div>
        </div>

        <x-admin::form.input name="tolerance" help="captcha.tolerance" type="number" label="Sapma payı (piksel)"
            :value="$values['tolerance'] ?? '6'"
            min="2" max="20"
            wrapper="mb-0" />
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">Önerilen: 6. Şikâyet gelirse 8-10 arası deneyin.</span>
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
