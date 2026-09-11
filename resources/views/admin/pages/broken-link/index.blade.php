@extends('admin.layout.app')
@section('admin.title', 'Kırık Linkler')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Kırık Linkler</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Kırık Linkler</li>
        </ol>
    </div>

    {{-- Özet kartlar. Sınıf adları tam yazılı — Tailwind taraması statik. --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-[15px] md:gap-[25px] mb-[25px]">
        @php
            $cards = [
                ['total', 'Kırık Bağlantı', 'link_off', 'bg-danger-100 dark:bg-[#15203c]', 'text-danger-500'],
                ['internal', 'Kendi Sayfalarımız', 'home', 'bg-primary-100 dark:bg-[#15203c]', 'text-primary-500'],
                ['external', 'Dış Siteler', 'public', 'bg-warning-100 dark:bg-[#15203c]', 'text-warning-600'],
                ['ignored', 'Yok Sayılan', 'visibility_off', 'bg-gray-100 dark:bg-[#15203c]', 'text-gray-500'],
            ];
        @endphp

        @foreach ($cards as [$key, $label, $icon, $chipClass, $iconClass])
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] md:p-[20px] rounded-md">
                <div class="trezo-card-content flex items-center gap-[12px]">
                    <span class="w-[42px] h-[42px] rounded-[12px] shrink-0 flex items-center justify-center {{ $chipClass }}">
                        <i class="material-symbols-outlined !text-[22px] {{ $iconClass }}">{{ $icon }}</i>
                    </span>
                    <div class="min-w-0">
                        <span class="block text-[11px] text-gray-500 dark:text-gray-400 leading-tight">{{ $label }}</span>
                        <span class="block text-lg font-bold text-black dark:text-white mt-[2px]" data-stat="{{ $key }}">{{ number_format($stats[$key]) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bu sayfa ile Yönlendirmeler sayfasının işi karışmasın diye kısa bir ayrım. --}}
    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-content grid grid-cols-1 lg:grid-cols-2 gap-[15px]">
            <div class="flex items-start gap-[12px] p-[16px] rounded-md bg-primary-50 dark:bg-[#15203c]">
                <span class="shrink-0 w-[36px] h-[36px] rounded-full bg-primary-500 text-white flex items-center justify-center">
                    <i class="material-symbols-outlined !text-[19px]">link_off</i>
                </span>
                <div class="text-xs text-gray-600 dark:text-gray-300 leading-[1.7]">
                    <strong class="block text-sm text-black dark:text-white mb-[4px]">Bu sayfa: kırık linkleri <em>bulur</em></strong>
                    Kendi içeriklerinizin <strong>içine yazdığınız</strong> bağlantıları ve görselleri tarar.
                    “Blog yazısındaki şu link tıklanınca hiçbir yere gitmiyor”, “bu görsel silinmiş”,
                    “menüdeki adres artık yok” gibi sorunları <strong>ziyaretçi karşılaşmadan önce</strong> yakalar.
                    Kaynak: sayfa/blog/hizmet içerikleri, menü öğeleri, tanıtım alanı butonu.
                </div>
            </div>

            <div class="flex items-start gap-[12px] p-[16px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                <span class="shrink-0 w-[36px] h-[36px] rounded-full bg-gray-400 text-white flex items-center justify-center">
                    <i class="material-symbols-outlined !text-[19px]">alt_route</i>
                </span>
                <div class="text-xs text-gray-600 dark:text-gray-300 leading-[1.7]">
                    <strong class="block text-sm text-black dark:text-white mb-[4px]">
                        <a href="{{ route('admin.redirect.index') }}" class="hover:text-primary-500 transition-all">Yönlendirmeler sayfası</a>:
                        kırık adresleri <em>onarır</em>
                    </strong>
                    Orası, <strong>dışarıdan</strong> gelen (Google sonucundaki eski bir adres, başka sitenin verdiği link)
                    ziyaretçiyi doğru sayfaya taşır. Yani burası “benim yazdığım link bozuk”, orası
                    “ziyaretçi olmayan bir adrese geldi”.
                    <span class="block mt-[6px] text-primary-500">
                        İkisi birleşir: burada kendi sitemize ait kırık bir adres bulunduğunda satırdaki
                        <strong>Yönlendir</strong> butonu, o adres için tek tuşla bir 301 yönlendirmesi oluşturur.
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md"
        data-broken-link
        data-scan-endpoint="{{ route('admin.broken-link.scan') }}">

        <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-start sm:justify-between">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Çalışmayan Adresler</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-[4px] max-w-[560px]">
                    Sayfa, blog ve hizmet içeriklerindeki bağlantılar ile görseller, menü adresleri ve
                    tanıtım alanının buton adresi denetlenir. Kendi sayfalarımıza giden kırık bir adresi
                    satırdaki “Yönlendir” ile tek tuşta 301'e bağlayabilirsiniz.
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-[6px]">
                    @if ($lastScan)
                        Son tarama: <strong class="text-black dark:text-white">{{ $lastScan['at']->format('d.m.Y H:i') }}</strong>
                        ({{ $lastScan['at']->diffForHumans() }}) — {{ number_format($lastScan['checked']) }} adres denetlendi.
                    @else
                        Henüz tarama yapılmadı. Tarama haftada bir kendiliğinden çalışır.
                    @endif
                </p>
            </div>

            <div class="trezo-card-subtitle mt-[15px] sm:mt-0 flex items-center gap-[10px] flex-wrap">
                <x-admin::activity-log-button module="broken-link" />

                <a href="{{ route('admin.broken-link.export') }}"
                    class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="material-symbols-outlined !text-[18px]">download</i> Dışa Aktar
                </a>

                @can('broken-link.scan')
                    <button type="button" id="broken-link-scan"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                        <i class="material-symbols-outlined !text-[19px]">radar</i> Şimdi Tara
                    </button>
                @endcan
            </div>
        </div>

        <div class="trezo-card-content">
            <div class="flex items-center gap-[10px] flex-wrap mb-[20px]">
                <div class="relative grow max-w-[240px]">
                    <input type="text" id="broken-link-search" placeholder="Adres ya da içerik ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>

                <select id="broken-link-scope" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm adresler</option>
                    @foreach ($scopes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="broken-link-kind" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Bağlantı + görsel</option>
                    @foreach ($kinds as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="broken-link-reason" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm sebepler</option>
                    @foreach ($reasons as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="broken-link-source" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm kaynaklar</option>
                    @foreach ($sources as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <label class="flex items-center gap-[8px] cursor-pointer select-none text-sm text-black dark:text-white">
                    <input type="checkbox" id="broken-link-ignored" class="w-[16px] h-[16px] accent-primary-500">
                    Yok sayılanları da göster
                </label>
            </div>

            <div class="table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md cursor-pointer relative" data-column="url">
                                Adres <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="source_label">
                                Nerede <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="reason">
                                Sebep <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="last_checked_at">
                                Son Kontrol <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" id="broken-link-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/broken-link/index.js') }}"></script>
@endpush
