@extends('admin.layout.app')
@section('admin.title', 'Site Haritası')

@php
    $in = $indexNow;
    $sitemapReady = (bool) $report;
@endphp

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">
            Site Haritası
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">ve arama motoru bildirimleri</span>
        </h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Site Haritası</li>
        </ol>
    </div>

    <div data-indexing-page
        data-generate-endpoint="{{ route('admin.sitemap.generate') }}"
        data-indexnow-submit="{{ route('admin.indexnow.submit') }}"
        data-indexnow-submit-all="{{ route('admin.indexnow.submit-all') }}"
        data-indexnow-key="{{ route('admin.indexnow.regenerate-key') }}">

        {{-- ───── Bu sayfa ne işe yarar ───── --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-content">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-[1.7] mb-[18px]">
                    Bir sayfa yayınladığınızda arama motorlarının onu kendi kendine bulması günler sürebilir.
                    Bu sayfadaki üç araç, sitenizin <strong>eksiksiz</strong> ve <strong>hızlı</strong>
                    bulunmasını sağlar. Üçü birbirinin yerine geçmez; birlikte çalışır.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-[15px]">
                    @php
                        $cards = [
                            [
                                'tab' => 'sitemap', 'icon' => 'lan', 'title' => 'Site Haritası',
                                'text' => 'Sitenizdeki tüm adreslerin listesi. Arama motoruna “bende şu sayfalar var” der. <strong>Tüm</strong> arama motorları kullanır.',
                                'ok' => $sitemapReady,
                                'okText' => $sitemapReady ? $report['total'].' adres listede' : 'Henüz oluşturulmadı',
                            ],
                            [
                                'tab' => 'indexnow', 'icon' => 'bolt', 'title' => 'Hızlı İndeksleme',
                                'text' => 'Bir sayfa değiştiği anda “gel bak” bildirimi gönderir. Bing, Yandex ve benzerleri destekler; <strong>Google desteklemez</strong>.',
                                'ok' => $in['enabled'],
                                'okText' => $in['enabled'] ? 'Açık' : 'Kapalı',
                            ],
                            [
                                'tab' => 'robots', 'icon' => 'policy', 'title' => 'robots.txt',
                                'text' => 'Robotlara “şuraya girme” der. Yönetim paneli gibi bölümlerin aramada çıkmasını engeller.',
                                'ok' => true,
                                'okText' => 'Yayında',
                            ],
                        ];
                    @endphp

                    @foreach ($cards as $card)
                        <button type="button" data-tab-jump="{{ $card['tab'] }}"
                            class="text-left p-[16px] rounded-md border border-gray-100 dark:border-[#172036] hover:border-primary-500 transition-all">
                            <div class="flex items-center gap-[10px] mb-[8px]">
                                <span class="shrink-0 w-[34px] h-[34px] rounded-full bg-primary-50 dark:bg-[#15203c] text-primary-500 flex items-center justify-center">
                                    <i class="material-symbols-outlined !text-[19px]">{{ $card['icon'] }}</i>
                                </span>
                                <span class="font-medium text-black dark:text-white">{{ $card['title'] }}</span>
                                <span class="ltr:ml-auto rtl:mr-auto text-[10px] font-medium py-[2px] px-[8px] rounded-sm shrink-0
                                    {{ $card['ok'] ? 'text-success-600 bg-success-100 dark:bg-[#ffffff14]' : 'text-warning-600 bg-warning-100 dark:bg-[#ffffff14]' }}">
                                    {{ $card['okText'] }}
                                </span>
                            </div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 leading-[1.65]">{!! $card['text'] !!}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ───── Sekmeler ───── --}}
        <div class="flex flex-wrap gap-[8px] mb-[20px]" data-tabs>
            <button type="button" data-tab="sitemap"
                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-sm rounded-md transition-all">
                <i class="material-symbols-outlined !text-[18px]">lan</i> Site Haritası
            </button>
            <button type="button" data-tab="indexnow"
                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-sm rounded-md transition-all">
                <i class="material-symbols-outlined !text-[18px]">bolt</i> Hızlı İndeksleme
            </button>
            <button type="button" data-tab="robots"
                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-sm rounded-md transition-all">
                <i class="material-symbols-outlined !text-[18px]">policy</i> robots.txt
            </button>
        </div>

        {{-- Site haritası + robots tek formda: ikisi de aynı ayar grubuna yazılır. --}}
        <form id="sitemap-form" action="{{ route('admin.sitemap.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ═══════════ SEKME: SİTE HARİTASI ═══════════ --}}
            <div data-pane="sitemap" hidden>

                {{-- Durum --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] flex items-center justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Durum</h5>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                                Site haritası önceden üretilip dosyaya yazılır — ziyaretçi geldiğinde hesaplanmaz, bu yüzden çok hızlıdır.
                            </span>
                        </div>
                        @can('sitemap.generate')
                            <button type="button" id="sitemap-generate"
                                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400 border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[18px]">autorenew</i>
                                Şimdi Yeniden Oluştur
                            </button>
                        @endcan
                    </div>
                    <div class="trezo-card-content">
                        @if ($sitemapReady)
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-[12px] mb-[18px]">
                                <div class="p-[14px] rounded-md bg-primary-50 dark:bg-[#15203c]">
                                    <span class="block text-xs text-primary-600 dark:text-gray-400 mb-[4px]">Toplam adres</span>
                                    <span class="block text-xl font-bold text-black dark:text-white">{{ $report['total'] }}</span>
                                </div>
                                @foreach ($sources as $key => $label)
                                    <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c] {{ ($enabled[$key] ?? true) ? '' : 'opacity-50' }}">
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[4px] truncate" title="{{ $label }}">{{ $label }}</span>
                                        <span class="block text-xl font-bold text-black dark:text-white">{{ $report['sources'][$key]['count'] ?? 0 }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-[8px] text-xs text-gray-500 dark:text-gray-400">
                                <i class="material-symbols-outlined !text-[16px]">schedule</i>
                                Son üretim: {{ $report['generated_at']->diffForHumans() }}
                                <span class="text-gray-400">({{ $report['generated_at']->format('d.m.Y H:i') }})</span>
                            </div>
                        @else
                            <div class="flex items-start gap-[10px] p-[14px] rounded-md bg-warning-50 border border-warning-200 dark:bg-[#15203c] dark:border-[#15203c] text-warning-600 text-sm">
                                <i class="material-symbols-outlined !text-[20px] shrink-0">info</i>
                                <span>
                                    Site haritası henüz oluşturulmadı. “Şimdi Yeniden Oluştur”a basın —
                                    ya da bir içerik kaydettiğinizde kendiliğinden oluşur.
                                </span>
                            </div>
                        @endif

                        {{-- Ne zaman kendiliğinden yenilenir --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-[12px] mt-[18px] pt-[18px] border-t border-gray-100 dark:border-[#172036]">
                            @foreach ([
                                ['edit_document', 'İçerik kaydedince', 'Sayfa, blog yazısı veya hizmet kaydettiğinizde 1 dakika içinde kendiliğinden yenilenir.'],
                                ['schedule', 'Her gece 04:00', 'Hiçbir şey yapmasanız da günde bir kez baştan üretilir. (Sunucuda zamanlayıcı kurulu olmalı.)'],
                                ['touch_app', 'Elle', 'Yukarıdaki butonla istediğiniz an yeniden oluşturabilirsiniz.'],
                            ] as [$icon, $title, $text])
                                <div class="flex items-start gap-[8px]">
                                    <i class="material-symbols-outlined !text-[17px] text-primary-500 shrink-0 mt-[1px]">{{ $icon }}</i>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 leading-[1.6]">
                                        <strong class="text-black dark:text-white block">{{ $title }}</strong>
                                        {{ $text }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Dosyalar --}}
                        <div class="mt-[18px] pt-[18px] border-t border-gray-100 dark:border-[#172036]">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[10px]">
                                Üretilen dosyalar — arama motoruna verilecek adres <strong>ilk sıradaki</strong>, diğerleri onun içinden bağlanır:
                            </span>
                            <div class="flex flex-wrap gap-[8px]">
                                <a href="{{ route('sitemap.index') }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-white bg-primary-500 hover:bg-primary-400 transition-all rounded-md">
                                    <i class="material-symbols-outlined !text-[16px]">open_in_new</i> sitemap.xml
                                </a>
                                @foreach ($sources as $key => $label)
                                    @foreach ($report['sources'][$key]['files'] ?? [] as $file)
                                        <a href="{{ url('/'.$file) }}" target="_blank" rel="noopener"
                                            class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-gray-500 dark:text-gray-400 transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                            <i class="material-symbols-outlined !text-[16px]">description</i> {{ $file }}
                                        </a>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kaynaklar --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">
                                Haritaya neler girsin?
                                <x-admin::form.help topic="sitemap.sources" />
                            </h5>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                                Kapattığınız grup haritadan çıkar — sayfalar yayında kalır, sadece bu listeyle bildirilmez.
                                Özel bir sebep yoksa hepsini açık bırakın.
                            </span>
                        </div>
                    </div>
                    <div class="trezo-card-content grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
                        @foreach ($sources as $key => $label)
                            <div class="flex items-center justify-between gap-[10px] p-[12px] rounded-md border border-gray-100 dark:border-[#172036]">
                                <div class="min-w-0">
                                    <x-admin::form.switch name="source_{{ $key }}" label="{{ $label }}"
                                        :checked="$enabled[$key] ?? true" wrapper="mb-0" />
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ $report['sources'][$key]['count'] ?? 0 }} adres
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Elle yönetilen adresler --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Elle yönetilen adresler</h5>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                                İstisnalar. Çoğu sitede boş kalır — emin değilseniz dokunmayın.
                            </span>
                        </div>
                    </div>
                    <div class="trezo-card-content grid grid-cols-1 lg:grid-cols-2 gap-[20px] md:gap-[25px]">
                        <x-admin::form.textarea name="excluded_urls" label="Haritaya girmesin" help="sitemap.excluded_urls"
                            :value="$excludedUrls" rows="5" placeholder="/ornek-kampanya" class="h-[130px]" wrapper="" />

                        <x-admin::form.textarea name="extra_urls" label="Haritaya ayrıca eklensin" help="sitemap.extra_urls"
                            :value="$extraUrls" rows="5" placeholder="https://siteniz.com/katalog.pdf" class="h-[130px]" wrapper="" />
                    </div>

                    @can('sitemap.update')
                        <div class="trezo-card-footer flex items-center justify-end -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                Kaydet
                            </button>
                        </div>
                    @endcan
                </div>
            </div>

            {{-- ═══════════ SEKME: ROBOTS.TXT ═══════════ --}}
            <div data-pane="robots" hidden>
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] flex items-start justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">
                                robots.txt
                                <x-admin::form.help topic="sitemap.robots_txt" />
                            </h5>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                                Arama motoru robotlarının sitenize girdiğinde ilk okuduğu dosya.
                            </span>
                        </div>
                        <a href="{{ route('robots') }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                            <i class="material-symbols-outlined !text-[16px]">open_in_new</i> Yayındaki hali
                        </a>
                    </div>

                    <div class="trezo-card-content grid grid-cols-1 lg:grid-cols-2 gap-[20px] md:gap-[25px]">
                        <div>
                            <x-admin::form.textarea name="robots_txt" label="İçerik" :value="$robotsBody" rows="8"
                                class="h-[190px] font-mono !text-[13px]" wrapper="" />

                            <div class="flex items-start gap-[8px] mt-[12px] p-[12px] rounded-md bg-gray-50 dark:bg-[#15203c] text-xs text-gray-500 dark:text-gray-400">
                                <i class="material-symbols-outlined !text-[16px] shrink-0 text-primary-500">auto_awesome</i>
                                <span>
                                    Site haritası satırını <strong>siz yazmayın</strong> — sistem, sitenin o anki adresiyle
                                    çıktının sonuna kendisi ekliyor:<br>
                                    <code class="text-black dark:text-white">Sitemap: {{ route('sitemap.index') }}</code>
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[10px] uppercase tracking-[.5px]">Satırlar ne anlama gelir</span>
                            <div class="space-y-[10px]">
                                @foreach ([
                                    ['User-agent: *', 'Aşağıdaki kurallar tüm arama motoru robotları için geçerli.'],
                                    ['Disallow: /admin', 'Bu adres ve altındakiler taranmasın. Yönetim panelinin aramada çıkmaması için.'],
                                    ['Allow: /ornek', 'Kapatılmış bir bölümün içinde tek bir adrese izin verir.'],
                                ] as [$code, $text])
                                    <div class="p-[12px] rounded-md border border-gray-100 dark:border-[#172036]">
                                        <code class="block text-xs text-primary-500 mb-[4px]">{{ $code }}</code>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 leading-[1.6]">{{ $text }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-start gap-[8px] mt-[12px] p-[12px] rounded-md bg-danger-50 dark:bg-[#15203c] border border-danger-100 dark:border-[#172036] text-xs text-danger-500">
                                <i class="material-symbols-outlined !text-[16px] shrink-0">warning</i>
                                <span>
                                    <strong>Dikkat:</strong> burada bir adresi kapatmak onu gizlemez, sadece taranmasını engeller.
                                    Bir sayfanın aramada <em>çıkmamasını</em> istiyorsanız doğru yer burası değil — o sayfanın
                                    SEO bölümündeki arama motoru ayarıdır. Yanlış bir <code>Disallow: /</code> satırı sitenizin
                                    tamamını aramadan düşürür.
                                </span>
                            </div>
                        </div>
                    </div>

                    @can('sitemap.update')
                        <div class="trezo-card-footer flex items-center justify-end -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                Kaydet
                            </button>
                        </div>
                    @endcan
                </div>
            </div>
        </form>

        {{-- ═══════════ SEKME: HIZLI İNDEKSLEME ═══════════ --}}
        <div data-pane="indexnow" hidden>

            {{-- Nasıl çalışır --}}
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-content">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-[15px] mb-[18px]">
                        @foreach ([
                            ['edit_note', '1. Siz içeriği kaydedersiniz', 'Bir sayfayı, blog yazısını veya hizmeti kaydettiğiniz anda sistem o adresi not eder.'],
                            ['send', '2. Bildirim gider', 'Adres, arama motorlarının ortak bildirim adresine gönderilir. Tek istek, hepsine ulaşır.'],
                            ['travel_explore', '3. Motor gelip bakar', 'Günler beklemek yerine genelde saatler içinde sayfanız yeniden taranır.'],
                        ] as [$icon, $title, $text])
                            <div class="flex items-start gap-[10px] p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                                <i class="material-symbols-outlined !text-[20px] text-primary-500 shrink-0">{{ $icon }}</i>
                                <span class="text-xs text-gray-500 dark:text-gray-400 leading-[1.65]">
                                    <strong class="block text-black dark:text-white mb-[2px]">{{ $title }}</strong>
                                    {{ $text }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-[8px] text-xs text-gray-500 dark:text-gray-400">
                        <span>Bildirimi alan motorlar:</span>
                        @foreach ($in['engines'] as $engine)
                            <span class="text-[11px] font-medium py-[2px] px-[8px] text-primary-600 bg-primary-100 dark:bg-[#ffffff14] rounded-sm">{{ $engine }}</span>
                        @endforeach
                    </div>

                    <div class="flex items-start gap-[8px] mt-[12px] p-[12px] rounded-md bg-gray-50 dark:bg-[#15203c] text-xs text-gray-500 dark:text-gray-400">
                        <i class="material-symbols-outlined !text-[16px] shrink-0 text-primary-500">info</i>
                        <span>
                            <strong>Google bu yöntemi desteklemiyor.</strong> Google tarafında aynı işi
                            <a href="{{ route('admin.search-console.index') }}" class="text-primary-500 hover:underline">Search Console</a>
                            ekranından site haritanızı bildirerek yaparsınız. Bu yüzden ikisi birlikte kullanılır.
                        </span>
                    </div>
                </div>
            </div>

            {{-- Ayarlar --}}
            <form id="indexnow-form" action="{{ route('admin.indexnow.update') }}" method="POST"
                class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                @csrf
                @method('PUT')

                <div class="trezo-card-header mb-[20px]">
                    <div class="trezo-card-title"><h5 class="!mb-0">Ayarlar</h5></div>
                </div>
                <div class="trezo-card-content">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
                        <div class="p-[14px] rounded-md border border-gray-100 dark:border-[#172036]">
                            <x-admin::form.switch name="enabled" label="Hızlı indeksleme açık" help="indexnow.enabled"
                                :checked="$in['enabled']" wrapper="mb-0" />
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[8px] leading-[1.6]">
                                Kapalıyken hiçbir bildirim gönderilmez. Ücretsizdir ve bir riski yoktur; açık bırakmanız önerilir.
                            </span>
                        </div>
                        <div class="p-[14px] rounded-md border border-gray-100 dark:border-[#172036]">
                            <x-admin::form.switch name="auto_submit" label="İçerik kaydedilince kendiliğinden bildir"
                                help="indexnow.auto_submit" :checked="$in['autoSubmit']" wrapper="mb-0" />
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[8px] leading-[1.6]">
                                Yayından kaldırdığınız ve sildiğiniz adresler de bildirilir — motor gidip sayfanın
                                olmadığını görür ve arama sonuçlarından düşürür.
                            </span>
                        </div>
                    </div>

                    {{-- Anahtar --}}
                    <div class="mt-[18px] p-[16px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                        <div class="flex items-start justify-between gap-[12px] flex-wrap">
                            <div class="min-w-0">
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[6px]">
                                    Doğrulama anahtarı
                                    <x-admin::form.help topic="indexnow.key" />
                                </span>
                                <code class="text-xs text-black dark:text-white break-all" data-indexnow-key-text>{{ $in['key'] }}</code>
                                <a href="{{ $in['keyUrl'] }}" target="_blank" rel="noopener" data-indexnow-key-url
                                    class="flex items-center gap-[4px] text-xs text-primary-500 hover:underline mt-[8px]">
                                    <i class="material-symbols-outlined !text-[15px]">open_in_new</i>
                                    Anahtar dosyasını aç
                                </a>
                            </div>
                            @can('indexnow.regenerate-key')
                                <button type="button" id="indexnow-new-key"
                                    class="inline-flex items-center gap-[6px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-gray-50 dark:hover:bg-[#172036]">
                                    <i class="material-symbols-outlined !text-[16px]">autorenew</i>
                                    Anahtarı yenile
                                </button>
                            @endcan
                        </div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[10px] leading-[1.6]">
                            Arama motoru, bildirimin gerçekten site sahibinden geldiğini bu koddan anlar.
                            Sunucuya elle dosya koymanız gerekmez — sistem bu adresi kendisi sunuyor.
                        </span>
                    </div>
                </div>

                @can('indexnow.update')
                    <div class="trezo-card-footer flex items-center justify-end -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
                        <button type="submit"
                            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                            Kaydet
                        </button>
                    </div>
                @endcan
            </form>

            {{-- Elle bildirim --}}
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] flex items-start justify-between gap-[12px] flex-wrap">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Elle bildirim
                            <x-admin::form.help topic="indexnow.manual" />
                        </h5>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                            Normalde gerekmez — içerik kaydedildikçe bildirim zaten gidiyor.
                        </span>
                    </div>
                    @can('indexnow.submit-all')
                        <button type="button" id="indexnow-submit-all"
                            class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                            <i class="material-symbols-outlined !text-[18px]">playlist_add_check</i>
                            Tüm adresleri bildir ({{ $in['sitemapUrlCount'] }})
                        </button>
                    @endcan
                </div>
                <div class="trezo-card-content">
                    <x-admin::form.textarea name="urls" label="Adresler" :value="null" rows="4"
                        placeholder="/hakkimizda&#10;/blog/yeni-yazi" class="h-[110px]" wrapper="" />

                    <div class="flex items-center justify-between gap-[12px] mt-[12px] flex-wrap">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Her satıra bir adres. Sadece yol yazmanız yeterli — tam adrese kendiliğinden çevrilir.
                        </span>
                        @can('indexnow.submit')
                            <button type="button" id="indexnow-submit"
                                class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[18px]">send</i>
                                Bildir
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Geçmiş --}}
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px]">
                    <div class="trezo-card-title"><h5 class="!mb-0">Gönderim geçmişi</h5></div>
                </div>
                <div class="trezo-card-content">
                    @if ($in['history'] === [])
                        <div class="text-center py-[30px]">
                            <i class="material-symbols-outlined !text-[38px] text-gray-300 dark:text-gray-600">history</i>
                            <span class="block text-sm text-gray-400 mt-[8px]">Henüz bildirim gönderilmedi.</span>
                        </div>
                    @else
                        <div class="table-responsive overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-black dark:text-white">
                                    <tr>
                                        <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] first:rounded-tl-md">Zaman</th>
                                        <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c]">Kaynak</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Adres</th>
                                        <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] last:rounded-tr-md">Sonuç</th>
                                    </tr>
                                </thead>
                                <tbody class="text-black dark:text-white">
                                    @foreach ($in['history'] as $entry)
                                        <tr class="border-b border-gray-100 dark:border-[#172036] last:border-0">
                                            <td class="py-[9px] px-[12px] whitespace-nowrap">
                                                {{ $entry['at']->format('d.m.Y H:i') }}
                                                <span class="block text-[11px] text-gray-400">{{ $entry['at']->diffForHumans() }}</span>
                                            </td>
                                            <td class="py-[9px] px-[12px] whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $entry['source'] }}</td>
                                            <td class="py-[9px] px-[12px] text-right font-medium whitespace-nowrap">{{ $entry['count'] }}</td>
                                            <td class="py-[9px] px-[12px]">
                                                @if ($entry['ok'])
                                                    <span class="text-[10px] font-medium py-[1px] px-[8px] text-success-600 bg-success-100 dark:bg-[#ffffff14] inline-block rounded-sm">Başarılı</span>
                                                @else
                                                    <span class="text-[10px] font-medium py-[1px] px-[8px] text-danger-500 bg-danger-100 dark:bg-[#ffffff14] inline-block rounded-sm">Hata</span>
                                                @endif
                                                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-[3px]">{{ $entry['message'] }}</span>
                                                @if ($entry['sample'] !== [])
                                                    <span class="block text-[11px] text-gray-400 truncate max-w-[320px]">{{ implode(', ', $entry['sample']) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/sitemap/index.js') }}"></script>
@endpush
