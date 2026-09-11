@extends('admin.layout.app')
@section('admin.title', 'Search Console')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">
            Search Console
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Google arama performansı)</span>
        </h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Search Console</li>
        </ol>
    </div>

    @unless ($credentialsReady)
        {{-- Kimlik GA4 ile ortak: service account JSON yoksa burada yapılacak bir şey yok. --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[40px] rounded-md text-center">
            <i class="material-symbols-outlined !text-[48px] text-gray-300 dark:text-gray-600">travel_explore</i>
            <h5 class="!mt-[16px] !mb-[8px]">Önce Google bağlantısı gerekiyor</h5>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-[460px] mx-auto mb-[20px]">
                Search Console, Analitik bölümüne yüklediğiniz <strong>aynı</strong> Google hesap dosyasını
                (service account JSON) kullanır. İkinci bir dosya yüklemeniz gerekmez — önce o bağlantıyı kurun.
            </p>
            <a href="{{ route('admin.setting.edit', 'analytics') }}"
                class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md hover:bg-primary-400 transition-all">
                <i class="material-symbols-outlined !text-[19px]">settings</i>
                Google bağlantısını kur
            </a>
        </div>
    @else
        @php
            $steps = [
                ['icon' => 'add_home_work', 'title' => 'Siteyi Search Console’a ekleyin', 'body' => 'search.google.com/search-console adresine girin, “Mülk ekle” deyin. “Alan adı” (tüm alt alanları kapsar) ya da “URL öneki” seçebilirsiniz.'],
                ['icon' => 'verified', 'title' => 'Site sahipliğini doğrulayın', 'body' => 'En kolay yol HTML etiketi: Google’ın verdiği doğrulama kodunu <strong>Ayarlar → Takip Kodları</strong> ekranındaki “Google site doğrulama” alanına yapıştırın, kaydedin, sonra Google’da “Doğrula”ya basın.'],
                ['icon' => 'person_add', 'title' => 'Panelin hesabını kullanıcı olarak ekleyin', 'body' => 'Search Console → <strong>Ayarlar → Kullanıcılar ve izinler → Kullanıcı ekle</strong>. Aşağıda yazan e-posta adresini ekleyin ve izni <strong>“Tam”</strong> seçin. “Tam” olmazsa site haritası gönderemezsiniz.'],
                ['icon' => 'api', 'title' => 'Search Console API’sini açın', 'body' => 'Google Cloud Console’da, Analitik için kullandığınız projede “Google Search Console API”yi aratıp <strong>Etkinleştir</strong>’e basın.'],
                ['icon' => 'link', 'title' => 'Site adresini buraya yazın', 'body' => 'Aşağıdaki alana mülk adresini yazın ya da “Mülkleri listele” ile hesabın erişebildiklerini getirip seçin. Sonra “Bağlantıyı test et”.'],
            ];
        @endphp

        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px]">
                <div class="trezo-card-title"><h5 class="!mb-0">Kurulum adımları</h5></div>
            </div>
            <div class="trezo-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-[15px]">
                    @foreach ($steps as $index => $step)
                        <div class="flex gap-[12px] p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                            <span class="shrink-0 w-[32px] h-[32px] rounded-full bg-primary-500 text-white flex items-center justify-center">
                                <i class="material-symbols-outlined !text-[18px]">{{ $step['icon'] }}</i>
                            </span>
                            <div>
                                <span class="block text-sm font-medium text-black dark:text-white mb-[4px]">
                                    {{ $index + 1 }}. {{ $step['title'] }}
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 leading-[1.6]">{!! $step['body'] !!}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px] mt-[18px]">
                    <div class="p-[14px] rounded-md border border-gray-100 dark:border-[#172036]">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[4px]">Search Console’a eklenecek e-posta</span>
                        <code class="text-xs text-black dark:text-white break-all">{{ $clientEmail ?? '—' }}</code>
                    </div>
                    <div class="p-[14px] rounded-md border border-gray-100 dark:border-[#172036]">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[4px]">Site doğrulama kodu</span>
                        @if ($verificationReady)
                            <span class="inline-flex items-center gap-[4px] text-xs text-success-600">
                                <i class="material-symbols-outlined !text-[15px]">check_circle</i> Girilmiş
                            </span>
                        @else
                            <a href="{{ route('admin.setting.edit', 'tracking') }}"
                                class="inline-flex items-center gap-[4px] text-xs text-primary-500 hover:underline">
                                <i class="material-symbols-outlined !text-[15px]">edit</i> Henüz girilmedi — Takip Kodları’na git
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Bağlantı --}}
        <form id="search-console-form" action="{{ route('admin.search-console.update') }}" method="POST"
            class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md"
            data-sites-endpoint="{{ route('admin.search-console.sites') }}"
            data-test-endpoint="{{ route('admin.search-console.test') }}">
            @csrf
            @method('PUT')

            <div class="trezo-card-header mb-[20px]">
                <div class="trezo-card-title"><h5 class="!mb-0">Bağlantı</h5></div>
            </div>
            <div class="trezo-card-content">
                <x-admin::form.input name="site_url" label="Search Console site adresi" help="search_console.site_url"
                    :value="$siteUrl" placeholder="sc-domain:siteniz.com" wrapper="" />

                <div class="flex flex-wrap gap-[10px] mt-[15px]">
                    <button type="button" id="sc-list-sites"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-sm text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px]">format_list_bulleted</i>
                        Mülkleri listele
                    </button>
                    <button type="button" id="sc-test"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-sm text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px]">wifi_tethering</i>
                        Bağlantıyı test et
                    </button>
                    @can('search-console.update')
                        <button type="submit"
                            class="inline-block py-[9px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                            Kaydet
                        </button>
                    @endcan
                </div>

                <div class="mt-[15px] hidden" data-sc-sites>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[8px]">Erişilebilen mülkler — tıklayıp seçin:</span>
                    <div class="flex flex-wrap gap-[8px]" data-sc-sites-list></div>
                </div>
            </div>
        </form>

        @if ($configured)
            <div data-search-console
                data-performance="{{ route('admin.search-console.performance') }}"
                data-sitemaps="{{ route('admin.search-console.sitemaps') }}"
                data-submit="{{ route('admin.search-console.submit') }}"
                data-inspect="{{ route('admin.search-console.inspect') }}">

                {{-- Tarih aralığı --}}
                <div class="flex items-center justify-between flex-wrap gap-[12px] mb-[20px]">
                    <div class="inline-flex rounded-md border border-gray-200 dark:border-[#172036] overflow-hidden" data-range>
                        @foreach ($ranges as $days)
                            <button type="button" data-days="{{ $days }}"
                                class="py-[7px] px-[16px] text-sm transition-all @unless ($loop->last) border-r border-gray-200 dark:border-[#172036] @endunless">
                                {{ $days }} gün
                            </button>
                        @endforeach
                    </div>
                    <span class="text-xs text-gray-400" data-sc-window></span>
                </div>

                {{-- KPI kartları --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-[15px] mb-[25px]" data-sc-kpis>
                    @for ($i = 0; $i < 4; $i++)
                        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md">
                            <span class="block h-[10px] w-[60%] bg-gray-100 dark:bg-[#172036] rounded animate-pulse mb-[10px]"></span>
                            <span class="block h-[18px] w-[40%] bg-gray-100 dark:bg-[#172036] rounded animate-pulse"></span>
                        </div>
                    @endfor
                </div>

                {{-- Eğilim --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[10px] flex items-center justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title"><h5 class="!mb-0">Tıklama &amp; gösterim eğilimi</h5></div>
                        <span class="text-xs text-gray-400">
                            Google verisi yaklaşık {{ $lagDays }} gün gecikmeli — son günler grafikte yer almaz.
                        </span>
                    </div>
                    <div id="sc-trend" class="min-h-[300px]"></div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-[25px] mb-[25px]">
                    {{-- Sorgular --}}
                    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                        <div class="trezo-card-header mb-[15px]">
                            <div class="trezo-card-title">
                                <h5 class="!mb-0">
                                    Hangi aramalarda çıkıyorsunuz
                                    <x-admin::form.help topic="search_console.queries" />
                                </h5>
                            </div>
                        </div>
                        <div class="table-responsive overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-black dark:text-white">
                                    <tr>
                                        <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] first:rounded-tl-md">Arama</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Tıklama</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Gösterim</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Oran</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right last:rounded-tr-md">Sıra</th>
                                    </tr>
                                </thead>
                                <tbody data-sc-queries class="text-black dark:text-white"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Sayfalar --}}
                    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                        <div class="trezo-card-header mb-[15px]">
                            <div class="trezo-card-title"><h5 class="!mb-0">Aramadan en çok tıklanan sayfalar</h5></div>
                        </div>
                        <div class="table-responsive overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-black dark:text-white">
                                    <tr>
                                        <th class="font-medium ltr:text-left rtl:text-right px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] first:rounded-tl-md">Sayfa</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Tıklama</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Gösterim</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right">Oran</th>
                                        <th class="font-medium px-[12px] py-[9px] bg-gray-50 dark:bg-[#15203c] text-right last:rounded-tr-md">Sıra</th>
                                    </tr>
                                </thead>
                                <tbody data-sc-pages class="text-black dark:text-white"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-[25px] mb-[25px]">
                    {{-- Ülkeler --}}
                    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                        <div class="trezo-card-header mb-[15px]">
                            <div class="trezo-card-title"><h5 class="!mb-0">Ülkeler</h5></div>
                        </div>
                        <ul data-sc-countries class="space-y-[10px] text-sm text-black dark:text-white"></ul>
                    </div>

                    {{-- Cihazlar --}}
                    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                        <div class="trezo-card-header mb-[10px]">
                            <div class="trezo-card-title"><h5 class="!mb-0">Cihazlar</h5></div>
                        </div>
                        <div id="sc-devices" class="min-h-[280px]"></div>
                    </div>
                </div>

                {{-- Site haritaları --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] flex items-center justify-between gap-[12px] flex-wrap">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">
                                Site haritaları
                                <x-admin::form.help topic="search_console.sitemaps" />
                            </h5>
                        </div>
                        @can('search-console.submit')
                            <button type="button" id="sc-submit-sitemap"
                                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-white transition-all rounded-md bg-primary-500 hover:bg-primary-400 border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[18px]">upload_file</i>
                                Site haritamızı gönder
                            </button>
                        @endcan
                    </div>
                    <div class="trezo-card-content">
                        <div data-sc-sitemaps>
                            <span class="text-sm text-gray-400">Yükleniyor…</span>
                        </div>
                    </div>
                </div>

                {{-- URL denetimi --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">
                                URL denetimi
                                <x-admin::form.help topic="search_console.inspect" />
                            </h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <div class="sm:flex items-start gap-[10px]">
                            <div class="flex-1">
                                <input type="text" id="sc-inspect-url" placeholder="/hakkimizda"
                                    class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500">
                                <span class="text-danger-500 text-xs mt-[6px] block" data-error="url"></span>
                            </div>
                            <button type="button" id="sc-inspect"
                                class="mt-[10px] sm:mt-0 inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                <i class="material-symbols-outlined !text-[18px]">travel_explore</i>
                                Denetle
                            </button>
                        </div>

                        <div class="mt-[20px]" data-sc-inspect-result></div>
                    </div>
                </div>
            </div>
        @endif
    @endunless
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/search-console/index.js') }}"></script>
@endpush
