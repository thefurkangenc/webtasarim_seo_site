@extends('admin.layout.guest')
@section('admin.title', 'Kurulum')

@section('content')
    @php
        $payload = $payload ?? [];
        $logoPreview = $logoPreview ?? null;
    @endphp
    <div class="min-h-screen bg-gray-50 dark:bg-[#0a0e19] py-[40px] md:py-[60px] px-[15px]"
        data-setup
        data-payload='@json($payload)'
        data-run="{{ route('setup.run') }}"
        data-admin="{{ route('setup.admin') }}"
        data-company="{{ route('setup.company') }}"
        data-logo="{{ route('setup.logo') }}"
        data-modules="{{ route('setup.modules') }}"
        data-contact="{{ route('setup.contact') }}"
        data-mail="{{ route('setup.mail') }}"
        data-legal="{{ route('setup.legal') }}"
        data-tasks='@json($tasks)'>

        <div class="mx-auto max-w-[920px]">
            <div class="text-center mb-[28px]">
                <img src="{{ asset('assets/admin/images/etkisoft-logo.svg') }}" alt="" class="inline-block dark:hidden h-[36px]">
                <img src="{{ asset('assets/admin/images/etkisoft-logo.svg') }}" alt="" class="hidden dark:inline-block h-[36px]">
                <h1 class="!font-semibold !text-[22px] md:!text-xl !mt-[18px] !mb-[6px] text-black dark:text-white">Site kurulumu</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Birkaç adımda paneli bu kuruma göre ayarlayın.</p>
            </div>

            @unless ($ready)
                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[25px] rounded-md">
                    <p class="text-black dark:text-white font-medium mb-[8px]">Veritabanı henüz hazır değil.</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-[12px]">Sunucuda sırasıyla şunları çalıştırın, sonra bu sayfayı yenileyin:</p>
                    <code class="block text-xs bg-gray-50 dark:bg-[#15203c] p-[14px] rounded-md text-black dark:text-white">php artisan migrate --force<br>php artisan db:seed --force</code>
                </div>
            @else
                {{-- Adım göstergesi --}}
                <ol class="hidden md:flex items-start justify-between gap-[8px] mb-[22px]" data-setup-nav>
                    @foreach ($steps as $index => $step)
                        <li class="flex-1 text-center" data-nav="{{ $step['key'] }}">
                            <span class="mx-auto mb-[8px] w-[32px] h-[32px] rounded-full flex items-center justify-center text-xs font-semibold bg-gray-200 dark:bg-[#15203c] text-gray-500 dark:text-gray-400"
                                data-nav-dot>
                                {{ $index + 1 }}
                            </span>
                            <span class="block text-[11px] text-gray-500 dark:text-gray-400">{{ $step['label'] }}</span>
                        </li>
                    @endforeach
                </ol>

                <div class="h-[6px] rounded-full bg-gray-200 dark:bg-[#15203c] overflow-hidden mb-[22px]">
                    <div class="h-full bg-primary-500 transition-all duration-500" data-setup-progress style="width: 8%"></div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[28px] rounded-md" data-setup-wizard>
                    {{-- 1. Yönetici --}}
                    <form data-step="admin" data-endpoint="{{ route('setup.admin') }}">
                        <h2 class="!text-lg !mb-[6px] text-black dark:text-white">Süper yönetici</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px]">Panele ilk giren hesap. Bu e-posta ve parola varsayılan bir hesapla gelmez.</p>
                        <x-admin::form.input name="name" label="Ad soyad" required placeholder="Örn. Ayşe Yılmaz" :value="$payload['admin']['name'] ?? ''" />
                        <x-admin::form.input name="email" type="email" label="E-posta" required placeholder="ornek@sirket.com" :value="$payload['admin']['email'] ?? ''" />
                        <x-admin::form.input name="password" type="password" label="Parola" required autocomplete="new-password" />
                        <x-admin::form.input name="password_confirmation" type="password" label="Parola tekrar" required autocomplete="new-password" wrapper="mb-0" />
                        <div class="flex justify-end mt-[24px]">
                            <button type="submit" class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                                Devam et <i class="material-symbols-outlined !text-[18px]">arrow_forward</i>
                            </button>
                        </div>
                    </form>

                    {{-- 2. Firma --}}
                    <form data-step="company" data-endpoint="{{ route('setup.company') }}" class="hidden">
                        <h2 class="!text-lg !mb-[6px] text-black dark:text-white">Firma bilgileri</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px]">Ad, e-posta ve logo sitede ve e-postalarda görünür. Favicon da logodan türetilir.</p>

                        <div class="mb-[20px]">
                            <label class="mb-[10px] text-black dark:text-white font-medium block">Logo</label>
                            <div class="flex items-center gap-[14px]">
                                <span data-logo-preview class="{{ $logoPreview ? '' : 'hidden' }} w-[56px] h-[56px] rounded-md border border-gray-200 dark:border-[#172036] overflow-hidden bg-gray-50 dark:bg-[#15203c]">
                                    <img alt="" class="w-full h-full object-contain" @if ($logoPreview) src="{{ $logoPreview }}" @endif>
                                </span>
                                <label class="inline-flex items-center gap-[6px] py-[9px] px-[14px] text-sm rounded-md border border-gray-200 dark:border-[#172036] text-black dark:text-white cursor-pointer hover:border-primary-500">
                                    <i class="material-symbols-outlined !text-[18px]">upload</i>
                                    Görsel seç
                                    <input type="file" accept="image/*" data-logo-input class="hidden">
                                </label>
                            </div>
                            <input type="hidden" name="logo_media_id" value="{{ $payload['company']['logo_media_id'] ?? '' }}">
                            <span class="text-danger-500 text-xs mt-[6px] block" data-error="logo"></span>
                        </div>

                        <x-admin::form.input name="name" label="Firma adı" required placeholder="Örn. Kuzey Enerji" :value="$payload['company']['name'] ?? ''" />
                        <x-admin::form.input name="legal_name" label="Yasal unvan" placeholder="Boş bırakılabilir" :value="$payload['company']['legal_name'] ?? ''" />
                        <x-admin::form.input name="email" type="email" label="E-posta" required placeholder="info@sirket.com" :value="$payload['company']['email'] ?? ''" />
                        <x-admin::form.input name="phone" label="Telefon" placeholder="Örn. 0212 000 00 00" :value="$payload['company']['phone'] ?? ''" />
                        <x-admin::form.textarea name="address" label="Adres" rows="2" class="h-[80px]" :value="$payload['company']['address'] ?? ''" />
                        <x-admin::form.textarea name="short_description" label="Kısa açıklama" rows="2" class="h-[80px]" wrapper="mb-0" :value="$payload['company']['short_description'] ?? ''" />

                        <div class="flex items-center justify-between gap-[10px] mt-[24px]">
                            <button type="button" data-back class="text-sm text-gray-500 hover:text-primary-500">Geri</button>
                            <button type="submit" class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                                Devam et <i class="material-symbols-outlined !text-[18px]">arrow_forward</i>
                            </button>
                        </div>
                    </form>

                    {{-- 3. Modüller --}}
                    <form data-step="modules" data-endpoint="{{ route('setup.modules') }}" class="hidden">
                        <h2 class="!text-lg !mb-[6px] text-black dark:text-white">Hangi bölümler açık olsun?</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-[18px]">Sayfalar ve gelen talepler her sitede açıktır. Diğerlerini sonra Modül Yönetimi’nden değiştirebilirsiniz.</p>

                        <div class="flex flex-wrap gap-[8px] mb-[16px]">
                            <span class="text-[10px] font-medium py-[2px] px-[9px] text-primary-600 bg-primary-100 dark:bg-[#ffffff14] rounded-sm">Sayfalar — açık</span>
                            <span class="text-[10px] font-medium py-[2px] px-[9px] text-primary-600 bg-primary-100 dark:bg-[#ffffff14] rounded-sm">Gelen Talepler — açık</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px]">
                            @foreach ($modules as $module)
                                <label class="flex items-start gap-[12px] p-[14px] rounded-md border border-gray-200 dark:border-[#172036] cursor-pointer hover:border-primary-500">
                                    <input type="checkbox" name="modules[{{ $module['key'] }}]" value="1"
                                        @checked(filter_var($payload['modules'][$module['key']] ?? true, FILTER_VALIDATE_BOOLEAN))
                                        class="mt-[3px] w-[16px] h-[16px] accent-primary-500">
                                    <span>
                                        <span class="flex items-center gap-[6px] text-sm font-medium text-black dark:text-white">
                                            <i class="material-symbols-outlined !text-[18px] text-primary-500">{{ $module['icon'] }}</i>
                                            {{ $module['label'] }}
                                        </span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">{{ $module['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between gap-[10px] mt-[24px]">
                            <button type="button" data-back class="text-sm text-gray-500 hover:text-primary-500">Geri</button>
                            <button type="submit" class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                                Devam et <i class="material-symbols-outlined !text-[18px]">arrow_forward</i>
                            </button>
                        </div>
                    </form>

                    {{-- 4. İletişim --}}
                    <form data-step="contact" data-endpoint="{{ route('setup.contact') }}" class="hidden">
                        <h2 class="!text-lg !mb-[6px] text-black dark:text-white">İletişim formu</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px]">Formdan gelenler Gelen Talepler kutusuna düşer. Bu adımı atlayabilirsiniz; alıcı olarak firma e-postası kullanılır.</p>
                        <x-admin::form.input name="to_email" type="email" label="Bildirim adresi" placeholder="Firma e-postası kullanılacak" :value="$payload['contact']['to_email'] ?? ''" />
                        <x-admin::form.input name="cc_email" type="email" label="Bilgi (CC)" placeholder="İsteğe bağlı" :value="$payload['contact']['cc_email'] ?? ''" />
                        <x-admin::form.switch name="enabled" label="Formu açık tut" :checked="filter_var($payload['contact']['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN)" />
                        <x-admin::form.switch name="auto_reply_enabled" label="Otomatik yanıt gönder" :checked="filter_var($payload['contact']['auto_reply_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)" wrapper="mb-0" />
                        <div class="flex items-center justify-between gap-[10px] mt-[24px]">
                            <button type="button" data-back class="text-sm text-gray-500 hover:text-primary-500">Geri</button>
                            <div class="flex gap-[10px]">
                                <button type="button" data-skip class="py-[10px] px-[16px] text-sm rounded-md border border-gray-200 dark:border-[#172036] text-black dark:text-white hover:border-primary-500">Atla</button>
                                <button type="submit" class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                                    Devam et <i class="material-symbols-outlined !text-[18px]">arrow_forward</i>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- 5. E-posta --}}
                    <form data-step="mail" data-endpoint="{{ route('setup.mail') }}" class="hidden">
                        <h2 class="!text-lg !mb-[6px] text-black dark:text-white">E-posta (SMTP)</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px]">Yoksa atlayın. Form kaydı yine gelir, bildirim maili gitmez. Sonra Ayarlar → E-Posta’dan girilir.</p>
                        <x-admin::form.input name="host" label="SMTP sunucusu" required placeholder="smtp.sirket.com" :value="$payload['mail']['host'] ?? ''" />
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px]">
                            <x-admin::form.select name="encryption" label="Şifreleme" required :options="['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'Yok']" :value="$payload['mail']['encryption'] ?? 'tls'" placeholder="" wrapper="mb-0" plain />
                            <x-admin::form.input name="port" type="number" label="Port" required :value="$payload['mail']['port'] ?? 587" wrapper="mb-0" />
                        </div>
                        <x-admin::form.input name="username" label="Kullanıcı adı" required wrapper="mt-[20px]" :value="$payload['mail']['username'] ?? ''" />
                        <x-admin::form.input name="password" type="password" label="Şifre" required autocomplete="new-password" />
                        <x-admin::form.input name="from_name" label="Gönderen adı" :value="$payload['mail']['from_name'] ?? ''" />
                        <x-admin::form.input name="from_address" type="email" label="Gönderen e-posta" required wrapper="mb-0" :value="$payload['mail']['from_address'] ?? ''" />
                        <div class="flex items-center justify-between gap-[10px] mt-[24px]">
                            <button type="button" data-back class="text-sm text-gray-500 hover:text-primary-500">Geri</button>
                            <div class="flex gap-[10px]">
                                <button type="button" data-skip class="py-[10px] px-[16px] text-sm rounded-md border border-gray-200 dark:border-[#172036] text-black dark:text-white hover:border-primary-500">Atla</button>
                                <button type="submit" class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                                    Devam et <i class="material-symbols-outlined !text-[18px]">arrow_forward</i>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- 6. Yasal --}}
                    <form data-step="legal" data-endpoint="{{ route('setup.legal') }}" class="hidden">
                        <h2 class="!text-lg !mb-[6px] text-black dark:text-white">Yasal iskelet</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-[16px]">KVKK ve çerez politikası sayfalarına firmanızın adıyla bir taslak yazılır, çerez çubuğu açılır. Metin hukuki tavsiye değildir; Ayarlar → İçerikler’den güncellenir.</p>
                        <p class="text-sm text-black dark:text-white mb-[4px]">KVKK aydınlatma metni, çerez politikası ve çerez onay çubuğu.</p>
                        <div class="flex items-center justify-between gap-[10px] mt-[24px]">
                            <button type="button" data-back class="text-sm text-gray-500 hover:text-primary-500">Geri</button>
                            <div class="flex gap-[10px]">
                                <button type="button" data-skip class="py-[10px] px-[16px] text-sm rounded-md border border-gray-200 dark:border-[#172036] text-black dark:text-white hover:border-primary-500">Atla</button>
                                <button type="submit" class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                                    Taslakları oluştur <i class="material-symbols-outlined !text-[18px]">arrow_forward</i>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- 7. Özet --}}
                    <div data-step="summary" class="hidden">
                        <h2 class="!text-lg !mb-[6px] text-black dark:text-white">Özet</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-[18px]">Kurulumu başlatınca bu bilgiler kaydedilir. ffmpeg sistem paketi kurulmaz; varsa yeşil, yoksa size uygun komut gösterilir.</p>
                        <ul class="text-sm flex flex-col gap-[10px] mb-[8px]" data-summary-list></ul>
                        <div class="flex items-center justify-between gap-[10px] mt-[24px]">
                            <button type="button" data-back class="text-sm text-gray-500 hover:text-primary-500">Geri</button>
                            <button type="button" data-start
                                class="inline-flex items-center gap-[8px] py-[12px] px-[24px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400 font-medium">
                                <i class="material-symbols-outlined !text-[20px]">rocket_launch</i>
                                Kurulumu başlat
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Kurulum animasyonu --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[32px] rounded-md hidden" data-setup-install>
                    <div class="text-center mb-[28px]">
                        <span data-install-orb class="relative inline-flex items-center justify-center w-[72px] h-[72px] rounded-full bg-primary-50 dark:bg-[#15203c] mb-[14px]">
                            <span class="absolute inset-[6px] rounded-full border-2 border-primary-100 dark:border-[#172036] border-t-primary-500 animate-spin"></span>
                            <i class="material-symbols-outlined text-primary-500 !text-[28px]">rocket_launch</i>
                        </span>
                        <h2 class="!text-lg !mb-[4px] text-black dark:text-white" data-install-title>Panel kuruluyor</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400" data-install-subtitle>Bu işlem bir dakikadan kısa sürer.</p>
                    </div>
                    <ul class="flex flex-col gap-[10px]" data-install-list></ul>
                    <div data-ffmpeg-hint class="hidden mt-[18px] p-[14px] rounded-md bg-warning-50 dark:bg-[#15203c] border border-gray-200 dark:border-[#172036]">
                        <p class="text-sm text-black dark:text-white font-medium mb-[6px]">ffmpeg bu makinede yok</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-[8px]">Video kalite kopyaları üretilmez; orijinal dosya yine oynar. Terminalde:</p>
                        <code data-ffmpeg-command class="block text-xs bg-white dark:bg-[#0c1427] p-[10px] rounded-md text-black dark:text-white select-all"></code>
                    </div>
                    <div data-install-retry class="hidden text-center mt-[24px]">
                        <button type="button"
                            class="inline-flex items-center gap-[6px] py-[12px] px-[24px] bg-primary-500 text-white rounded-md border border-primary-500 hover:bg-primary-400 hover:border-primary-400">
                            Tekrar dene <i class="material-symbols-outlined !text-[18px]">refresh</i>
                        </button>
                    </div>
                    <div data-install-done class="hidden text-center mt-[24px]">
                        <a data-install-link href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-[6px] py-[12px] px-[24px] bg-success-500 text-white rounded-md border border-success-500 hover:bg-success-400">
                            Panele git <i class="material-symbols-outlined !text-[18px]">login</i>
                        </a>
                    </div>
                </div>
            @endunless
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('assets/admin/js/pages/setup/index.js') }}"></script>
@endpush
