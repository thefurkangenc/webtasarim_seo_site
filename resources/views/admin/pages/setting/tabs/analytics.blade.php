@php
    $connected = ! empty($values['has_service_account']);
@endphp

<form id="analytics-form" action="{{ route('admin.setting.analytics.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px] md:mb-[25px] leading-relaxed">
        Panelin <a href="{{ route('admin.analytics.index') }}" class="text-primary-500 hover:underline">Analitik</a>
        ekranı, ziyaretçi verinizi doğrudan <strong>Google Analytics 4</strong>'ten çeker. Bunun için Google'dan
        <strong>bir kez</strong> alacağınız bir kimlik dosyası gerekir; nasıl alınacağı aşağıda adım adım anlatılıyor.
        Ön yüze eklenen GA4 izleme kodu bundan ayrıdır — o
        <a href="{{ route('admin.setting.edit', 'tracking') }}" class="text-primary-500 hover:underline">İzleme Kodları</a>
        sekmesindedir.
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

    <x-admin::form.input name="property_id" label="GA4 Property ID" required help="analytics.property_id"
        :value="$values['property_id'] ?? null"
        placeholder="Örn. 493819123"
        inputmode="numeric" />
    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-[14px] mb-[20px] md:mb-[25px]">
        Google Analytics &rsaquo; Yönetici &rsaquo; Mülk Ayarları'nda yazan sayısal kimlik.
        <strong>G- ile başlayan ölçüm kimliği değildir.</strong>
    </p>

    <div class="mb-[20px] md:mb-[25px]">
        <label class="mb-[10px] text-black dark:text-white font-medium block">
            Google kimlik dosyası (JSON) {{ $connected ? '· değiştirmek için yeni dosya yükleyin' : '' }}
            <x-admin::form.help topic="analytics.service_account" />
        </label>
        <input type="file" name="service_account" accept="application/json,.json"
            class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-[14px] file:py-[9px] file:px-[16px] file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-500 file:text-white hover:file:bg-primary-400 file:cursor-pointer border border-gray-200 dark:border-[#172036] rounded-md p-[8px] bg-white dark:bg-[#0c1427]">
        <span class="text-danger-500 text-xs mt-[6px] block" data-error="service_account"></span>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-[6px]">
            Dosya sunucuda <strong>şifreli</strong> saklanır, panele bir daha gösterilmez.
        </p>
    </div>

    @php
        $gaSteps = [
            [
                'icon' => 'create_new_folder',
                'title' => 'Google Cloud\'da bir proje oluşturun',
                'body' => '<a href="https://console.cloud.google.com" target="_blank" rel="noopener">console.cloud.google.com</a> adresine girin. Üstteki proje seçiciden <strong>Yeni Proje</strong> &rsaquo; bir ad verin (örn. <em>Site Analitik</em>) &rsaquo; <strong>Oluştur</strong>. Zaten bir projeniz varsa onu kullanabilirsiniz.',
            ],
            [
                'icon' => 'bolt',
                'title' => '"Google Analytics Data API"yi etkinleştirin',
                'body' => 'Sol menü &rsaquo; <strong>API\'ler ve Hizmetler</strong> &rsaquo; <strong>Kitaplık</strong>. Arama kutusuna <code>Google Analytics Data API</code> yazın, çıkan sonuca tıklayıp <strong>Etkinleştir</strong>\'e basın. Etkinleşmesi 1–2 dakika sürebilir.',
            ],
            [
                'icon' => 'person_add',
                'title' => 'Bir servis hesabı oluşturun',
                'body' => 'Sol menü &rsaquo; <strong>IAM ve Yönetici</strong> &rsaquo; <strong>Hizmet Hesapları</strong> &rsaquo; <strong>Hizmet hesabı oluştur</strong>. Bir ad verin (örn. <em>analitik-okuyucu</em>), <strong>Oluştur ve devam et</strong> deyin, rol adımını atlayıp <strong>Bitti</strong>\'ye basın. Google size <code>...@...iam.gserviceaccount.com</code> biçiminde bir e-posta üretir.',
            ],
            [
                'icon' => 'key',
                'title' => 'Kimlik dosyasını (JSON) indirin',
                'body' => 'Listeden yeni hesabı açın &rsaquo; <strong>Anahtarlar</strong> sekmesi &rsaquo; <strong>Anahtar ekle</strong> &rsaquo; <strong>Yeni anahtar oluştur</strong> &rsaquo; tür olarak <strong>JSON</strong> &rsaquo; <strong>Oluştur</strong>. Dosya bilgisayarınıza iner — <strong>kimseyle paylaşmayın</strong>.',
            ],
            [
                'icon' => 'group_add',
                'title' => 'Bu hesaba Google Analytics\'te izin verin',
                'body' => '<a href="https://analytics.google.com" target="_blank" rel="noopener">analytics.google.com</a> &rsaquo; sol altta <strong>Yönetici</strong> &rsaquo; <strong>Mülk</strong> sütununda <strong>Mülk Erişim Yönetimi</strong> &rsaquo; sağ üstteki <strong>+</strong> &rsaquo; <strong>Kullanıcı ekle</strong>. 3. adımdaki e-postayı yapıştırın, rol olarak <strong>Görüntüleyici</strong>\'yi seçin, <strong>Ekle</strong>\'ye basın.',
            ],
            [
                'icon' => 'tag',
                'title' => 'Mülk kimliğini (Property ID) bulun',
                'body' => 'Aynı <strong>Yönetici</strong> ekranında <strong>Mülk ayrıntıları</strong>\'na girin. Sağ üstte <strong>MÜLK KİMLİĞİ</strong> altında yazan sayıyı (örn. <code>493819123</code>) kopyalayın.',
            ],
            [
                'icon' => 'cloud_upload',
                'title' => 'Panele girin ve kaydedin',
                'body' => 'Yukarıdaki <strong>GA4 Property ID</strong> alanına 6. adımdaki sayıyı yazın, <strong>Google kimlik dosyası</strong> alanına 4. adımda inen dosyayı seçin, <strong>Kaydet</strong>\'e basın. Sonra <strong>Bağlantıyı test et</strong> ile doğrulayın.',
            ],
        ];
    @endphp

    <div class="rounded-md border border-gray-100 dark:border-[#172036] p-[16px] md:p-[20px] mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[8px] mb-[6px]">
            <i class="material-symbols-outlined !text-[20px] text-primary-500">install_desktop</i>
            <h6 class="!mb-0 font-semibold text-black dark:text-white">Kurulum — adım adım</h6>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-[18px] leading-relaxed">
            İlk kurulum ~10 dakika sürer ve yalnızca bir kez yapılır. İki Google ekranı arasında gidip geleceksiniz:
            <strong>Google Cloud Console</strong> (1–4. adımlar) ve <strong>Google Analytics</strong> (5–6. adımlar).
        </p>

        <ol class="space-y-[16px]">
            @foreach ($gaSteps as $i => $step)
                <li class="flex gap-[12px]">
                    <span class="shrink-0 relative w-[32px] h-[32px] rounded-full bg-primary-50 dark:bg-[#15203c] flex items-center justify-center">
                        <i class="material-symbols-outlined !text-[18px] text-primary-500">{{ $step['icon'] }}</i>
                        <span class="absolute -top-[4px] -right-[4px] w-[16px] h-[16px] rounded-full bg-primary-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                    </span>
                    <div class="pt-[3px] text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        <span class="block font-medium text-black dark:text-white mb-[2px]">{{ $step['title'] }}</span>
                        {!! $step['body'] !!}
                    </div>
                </li>
            @endforeach
        </ol>

        <p class="text-xs text-gray-500 dark:text-gray-400 mt-[18px] pt-[14px] border-t border-gray-100 dark:border-[#172036] leading-relaxed">
            <i class="material-symbols-outlined !text-[15px] align-middle text-gray-400">lightbulb</i>
            Menü adları Google tarafından ara sıra güncellenir; bir bağlantıyı bulamazsanız o ekranda arama kutusuna
            adımdaki koyu yazılı ifadeyi yazın.
        </p>
    </div>

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
