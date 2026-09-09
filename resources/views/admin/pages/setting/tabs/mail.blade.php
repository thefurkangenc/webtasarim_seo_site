@php
    $encryptionOptions = [
        'tls' => 'TLS (STARTTLS)',
        'ssl' => 'SSL',
        'none' => 'Yok',
    ];
    $hasPassword = (bool) ($values['has_password'] ?? false);
@endphp

<form id="setting-form" action="{{ route('admin.setting.mail.update') }}" method="POST" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-[20px] md:gap-[25px]">
        <div>
            <x-admin::form.input name="host" label="SMTP sunucusu" required
                :value="$values['host'] ?? null"
                placeholder="Örn. smtp.ornek.com" />

            <x-admin::form.input name="username" label="Kullanıcı adı" required
                :value="$values['username'] ?? null"
                placeholder="Örn. info@ornek.com"
                autocomplete="username" />

            <x-admin::form.input name="password" type="password" label="Şifre"
                :required="! $hasPassword"
                autocomplete="new-password"
                :placeholder="$hasPassword
                    ? 'Kayıtlı şifre korunuyor — değiştirmek için yeni şifre yazın'
                    : 'SMTP şifresi'" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]">
                <x-admin::form.select name="encryption" label="Şifreleme" required
                    :value="$values['encryption'] ?? 'tls'"
                    :options="$encryptionOptions"
                    placeholder=""
                    data-mail-encryption
                    wrapper="mb-0" />

                <x-admin::form.input name="port" type="number" label="Port" required
                    :value="$values['port'] ?? 587"
                    min="1" max="65535"
                    data-mail-port
                    wrapper="mb-0" />
            </div>
        </div>

        <div>
            <x-admin::form.input name="from_name" label="Gönderen adı"
                :value="$values['from_name'] ?? null"
                placeholder="Örn. Umay Dijital" />

            <x-admin::form.input name="from_address" label="Gönderen e-posta" required
                type="email"
                :value="$values['from_address'] ?? null"
                placeholder="Örn. info@ornek.com" />

            <div class="settings-panel">
                <div class="flex items-start gap-[12px] mb-[16px]">
                    <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                        <i class="material-symbols-outlined !text-[22px] text-primary-500">sync_alt</i>
                    </span>
                    <div class="min-w-0">
                        <p class="!mb-[3px] font-medium text-black dark:text-white">Bağlantı testi</p>
                        <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">
                            Girdiğiniz bilgilerle SMTP sunucusuna bağlanmayı dener. Kaydetmeniz gerekmez.
                        </p>
                    </div>
                </div>

                <div data-mail-test-status
                    class="hidden items-center gap-[8px] py-[1rem] px-[1rem] rounded-md mb-[16px] border">
                    <i data-mail-test-icon class="material-symbols-outlined !text-[20px] shrink-0"></i>
                    <p data-mail-test-result class="!mb-0 text-sm"></p>
                </div>

                <button type="button" data-mail-test
                    class="w-full inline-flex items-center justify-center gap-[6px] py-[10px] px-[18px] bg-white dark:bg-[#0c1427] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#0c1427] hover:border-primary-500 hover:text-primary-500">
                    <i class="material-symbols-outlined !text-[18px]">bolt</i>
                    Test Bağlantısı
                </button>
            </div>
        </div>
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
