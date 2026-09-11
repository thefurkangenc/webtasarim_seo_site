@extends('admin.layout.app')
@section('admin.title', 'Dashboard')

@php
    /*
     * Renk tonu -> sınıf eşlemesi. Tailwind taraması STATİKTİR: sınıf adı
     * "bg-{$tone}-50" gibi birleştirilirse derlemeye girmez ve sessizce
     * renksiz kalır. Bu yüzden her sınıf tam yazılı.
     */
    $tones = [
        'primary' => ['chip' => 'bg-primary-50 dark:bg-[#15203c] text-primary-500', 'border' => 'border-primary-500', 'text' => 'text-primary-500'],
        'success' => ['chip' => 'bg-success-50 dark:bg-[#15203c] text-success-600', 'border' => 'border-success-500', 'text' => 'text-success-600'],
        'warning' => ['chip' => 'bg-warning-50 dark:bg-[#15203c] text-warning-600', 'border' => 'border-warning-500', 'text' => 'text-warning-600'],
        'danger' => ['chip' => 'bg-danger-50 dark:bg-[#15203c] text-danger-500', 'border' => 'border-danger-500', 'text' => 'text-danger-500'],
        'info' => ['chip' => 'bg-info-50 dark:bg-[#15203c] text-info-500', 'border' => 'border-info-500', 'text' => 'text-info-500'],
    ];
    $card = 'trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md';
    $th = 'font-medium ltr:text-left rtl:text-right px-[15px] py-[10px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap text-xs';
    $td = 'px-[15px] py-[11px] border-b border-gray-100 dark:border-[#172036] text-sm';
@endphp

@section('content')
    {{-- ── Karşılama ───────────────────────────────────────────────── --}}
    <div class="mb-[25px] md:flex md:items-end md:justify-between gap-[15px]">
        <div>
            <h5 class="!mb-[4px]">Merhaba{{ auth()->user()?->name ? ', '.auth()->user()->name : '' }} 👋</h5>
            <p class="!mb-0 text-sm text-gray-500 dark:text-gray-400">
                {{ now()->translatedFormat('d F Y, l') }} · sitenin güncel durumu aşağıda.
            </p>
        </div>

        <div class="flex items-center gap-[10px] mt-[14px] md:mt-0">
            @can('analytics.realtime')
                {{-- Şu an sitede kaç kişi var. 30 sn'de bir tazelenir. --}}
                <span data-realtime hidden
                    class="items-center gap-[7px] py-[8px] px-[14px] rounded-md bg-success-50 dark:bg-[#15203c] text-success-600 text-sm font-medium">
                    <span class="relative flex w-[8px] h-[8px]">
                        <span class="absolute inline-flex w-full h-full rounded-full bg-success-500 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex w-[8px] h-[8px] rounded-full bg-success-500"></span>
                    </span>
                    <span data-realtime-count>0</span> kişi sitede
                </span>
            @endcan

            <a href="{{ url('/') }}" target="_blank"
                class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[18px]">open_in_new</i> Siteyi gör
            </a>
        </div>
    </div>

    {{-- ── Dikkat isteyenler ───────────────────────────────────────── --}}
    @if ($alerts)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-[15px] mb-[25px]">
            @foreach ($alerts as $alert)
                @can($alert['permission'])
                    @php
                        $tone = $tones[$alert['tone']];
                    @endphp
                    <a href="{{ $alert['route'] }}"
                        class="{{ $card }} !p-[18px] flex items-center gap-[14px] border ltr:border-l-[3px] rtl:border-r-[3px] {{ $tone['border'] }} border-y-gray-100 ltr:border-r-gray-100 rtl:border-l-gray-100 dark:border-y-[#172036] dark:ltr:border-r-[#172036] dark:rtl:border-l-[#172036] transition-all hover:shadow-3xl">
                        <span class="shrink-0 w-[40px] h-[40px] rounded-full {{ $tone['chip'] }} flex items-center justify-center">
                            <i class="material-symbols-outlined !text-[21px]">{{ $alert['icon'] }}</i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <span class="block font-medium text-black dark:text-white">{{ $alert['title'] }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">{{ $alert['body'] }}</span>
                        </div>
                        <span class="shrink-0 text-xs font-medium {{ $tone['text'] }} flex items-center gap-[3px]">
                            {{ $alert['action'] }} <i class="material-symbols-outlined !text-[16px]">chevron_right</i>
                        </span>
                    </a>
                @endcan
            @endforeach
        </div>
    @endif

    {{-- ── Dört büyük sayı ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-[15px] md:gap-[25px] mb-[25px]">
        @foreach ($counters as $counter)
            @can($counter['permission'])
                @php
                    $tone = $tones[$counter['tone']];
                @endphp
                <a href="{{ $counter['route'] }}"
                    class="{{ $card }} border border-transparent transition-all hover:border-primary-500">
                    <div class="flex items-start justify-between gap-[10px] mb-[14px]">
                        <span class="w-[42px] h-[42px] rounded-[12px] {{ $tone['chip'] }} flex items-center justify-center shrink-0">
                            <i class="material-symbols-outlined !text-[21px]">{{ $counter['icon'] }}</i>
                        </span>

                        @if ($counter['change'] !== null)
                            @php
                                $up = $counter['change'] >= 0;
                            @endphp
                            <span class="inline-flex items-center gap-[2px] py-[2px] px-[8px] rounded-sm text-[11px] font-medium {{ $up ? 'bg-success-100 dark:bg-[#15203c] text-success-600' : 'bg-danger-100 dark:bg-[#15203c] text-danger-500' }}">
                                <i class="material-symbols-outlined !text-[13px]">{{ $up ? 'trending_up' : 'trending_down' }}</i>
                                {{ $up ? '+' : '' }}{{ $counter['change'] }}%
                            </span>
                        @endif
                    </div>

                    <span class="block text-[26px] font-bold text-black dark:text-white leading-none mb-[6px]">
                        {{ $counter['value'] }}<span class="text-sm font-medium text-gray-400">{{ $counter['suffix'] ?? '' }}</span>
                    </span>
                    <span class="block text-sm font-medium text-black dark:text-white">{{ $counter['label'] }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[2px]">{{ $counter['hint'] }}</span>

                    @if ($counter['badge'] > 0)
                        <span class="inline-block mt-[10px] py-[2px] px-[8px] rounded-sm text-[11px] bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-400">
                            {{ $counter['badge'] }} {{ $counter['badge_label'] }}
                        </span>
                    @endif
                </a>
            @endcan
        @endforeach
    </div>

    {{-- ── Trafik + talep dağılımı ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-[25px] mb-[25px]">
        @can('analytics.data')
            <div class="xl:col-span-2 {{ $card }}"
                @if ($analyticsReady) data-traffic data-endpoint="{{ route('admin.analytics.data') }}" @endif>
                <div class="trezo-card-header mb-[20px] flex items-center justify-between gap-[12px] flex-wrap">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Site Trafiği</h5>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Google Analytics 4</span>
                    </div>

                    @if ($analyticsReady)
                        <div class="flex items-center gap-[10px]">
                            {{-- Aralık seçimi; her değişimde tek istek atılır (10 dk cache'li). --}}
                            <div class="flex rounded-md border border-gray-200 dark:border-[#172036] overflow-hidden">
                                @foreach ([7 => '7G', 28 => '28G', 90 => '90G'] as $value => $label)
                                    <button type="button" data-range="{{ $value }}"
                                        class="py-[6px] px-[12px] text-xs transition-all border-r border-gray-200 dark:border-[#172036] last:border-r-0 text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            <a href="{{ route('admin.analytics.index') }}"
                                class="inline-flex items-center gap-[4px] text-sm text-primary-500 hover:underline whitespace-nowrap">
                                Detay <i class="material-symbols-outlined !text-[16px]">arrow_forward</i>
                            </a>
                        </div>
                    @endif
                </div>

                @if ($analyticsReady)
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-[12px] mb-[18px]" data-kpis>
                        @for ($i = 0; $i < 4; $i++)
                            <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                                <span class="block h-[10px] w-[55%] bg-gray-200 dark:bg-[#172036] rounded animate-pulse mb-[10px]"></span>
                                <span class="block h-[16px] w-[35%] bg-gray-200 dark:bg-[#172036] rounded animate-pulse"></span>
                            </div>
                        @endfor
                    </div>
                    <div data-traffic-chart class="min-h-[260px]"></div>
                @else
                    {{-- Bağlantı yoksa boş bir grafik iskeleti göstermek anlamsız;
                         yerine ne yapılacağını söyleyen bir kart basılır. --}}
                    <div class="py-[30px] text-center">
                        <i class="material-symbols-outlined !text-[40px] text-gray-300 dark:text-gray-600">insights</i>
                        <span class="block font-medium text-black dark:text-white mt-[10px]">Trafik verisi için GA4 bağlantısı gerekiyor</span>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mt-[4px] mb-[16px]">
                            Service account dosyası ve property ID ile birkaç dakikada kurulur.
                        </span>
                        <a href="{{ route('admin.setting.edit', 'analytics') }}"
                            class="inline-flex items-center gap-[6px] py-[9px] px-[18px] bg-primary-500 text-white rounded-md hover:bg-primary-400 transition-all text-sm">
                            <i class="material-symbols-outlined !text-[18px]">settings</i> Bağlantıyı kur
                        </a>
                    </div>
                @endif
            </div>
        @endcan

        @can('lead.index')
            <div class="{{ $card }}">
                <div class="trezo-card-header mb-[20px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Talep Durumları</h5>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Tüm zamanlar</span>
                    </div>
                </div>

                @if (collect($leadStatuses)->sum('value') > 0)
                    <div data-lead-donut data-series="{{ json_encode($leadStatuses) }}" class="min-h-[240px]"></div>
                @else
                    <div class="py-[50px] text-center">
                        <i class="material-symbols-outlined !text-[36px] text-gray-300 dark:text-gray-600">inbox</i>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mt-[8px]">Henüz talep gelmedi.</span>
                    </div>
                @endif
            </div>
        @endcan
    </div>

    {{-- ── İçerik envanteri ────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-[25px] mb-[25px]">
        <div class="xl:col-span-2 {{ $card }}">
            <div class="trezo-card-header mb-[20px]">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">İçerik Envanteri</h5>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Her satırda yayındaki / toplam kayıt sayısı
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[12px]">
                @foreach ($content as $row)
                    @can($row['permission'])
                        @php
                            $ratio = $row['total'] > 0 ? round($row['live'] / $row['total'] * 100) : 0;
                        @endphp
                        <a href="{{ $row['route'] }}"
                            class="p-[14px] rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:border-primary-500">
                            <div class="flex items-center gap-[10px] mb-[10px]">
                                <i class="material-symbols-outlined !text-[19px] text-primary-500">{{ $row['icon'] }}</i>
                                <span class="text-sm font-medium text-black dark:text-white flex-1 truncate">{{ $row['label'] }}</span>
                                <span class="text-sm font-bold text-black dark:text-white">
                                    {{ $row['live'] }}<span class="text-xs font-normal text-gray-400">/{{ $row['total'] }}</span>
                                </span>
                            </div>
                            {{-- Yayında oranı: taslakta kalmış içerik yığını burada gözle görülür. --}}
                            <span class="block h-[5px] rounded-full bg-gray-100 dark:bg-[#15203c] overflow-hidden">
                                <span class="block h-full rounded-full bg-primary-500" style="width: {{ $ratio }}%"></span>
                            </span>
                        </a>
                    @endcan
                @endforeach
            </div>
        </div>

        @can('media.index')
            <div class="{{ $card }} flex flex-col">
                <div class="trezo-card-header mb-[20px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Medya Kütüphanesi</h5>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-[12px] mb-[16px]">
                    <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                        <span class="block text-[20px] font-bold text-black dark:text-white leading-none mb-[5px]">{{ $media['count'] }}</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">dosya</span>
                    </div>
                    <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                        <span class="block text-[20px] font-bold text-black dark:text-white leading-none mb-[5px]">{{ $media['size'] }}</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">kullanılan alan</span>
                    </div>
                </div>

                <span class="block text-xs text-gray-500 dark:text-gray-400 mb-[16px]">
                    Bunların {{ $media['images'] }} tanesi görsel. Hiçbir kayda bağlı olmayan dosyaları
                    kütüphanedeki “Bağlantısız” filtresiyle bulup temizleyebilirsiniz.
                </span>

                <a href="{{ $media['route'] }}"
                    class="mt-auto inline-flex items-center justify-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="material-symbols-outlined !text-[18px]">perm_media</i> Kütüphaneyi aç
                </a>
            </div>
        @endcan
    </div>

    {{-- ── Üretim eğilimi ──────────────────────────────────────────── --}}
    <div class="{{ $card }} mb-[25px]">
        <div class="trezo-card-header mb-[20px]">
            <div class="trezo-card-title">
                <h5 class="!mb-0">İçerik Üretimi</h5>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Son 12 ayda eklenen kayıtlar — düzenli yayın, arama motorlarında en çok işe yarayan alışkanlıktır.
                </span>
            </div>
        </div>
        <div data-production data-payload="{{ json_encode($production) }}" class="min-h-[280px]"></div>
    </div>

    {{-- ── Talepler / en çok görüntülenen / etkinlik ───────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-[25px] mb-[25px]">
        @can('lead.index')
            <div class="{{ $card }}">
                <div class="trezo-card-header mb-[18px] flex items-center justify-between gap-[10px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Son Gelen Talepler</h5>
                    </div>
                    <a href="{{ route('admin.lead.index') }}" class="text-xs text-primary-500 hover:underline whitespace-nowrap">Tümü</a>
                </div>

                @if ($recentLeads)
                    <ul class="flex flex-col">
                        @foreach ($recentLeads as $lead)
                            <li class="border-b border-gray-100 dark:border-[#172036] last:border-0">
                                <a href="{{ $lead['url'] }}" class="flex items-start gap-[10px] py-[11px] group">
                                    <span class="shrink-0 w-[32px] h-[32px] rounded-full bg-gray-100 dark:bg-[#15203c] text-gray-500 dark:text-gray-400 flex items-center justify-center text-xs font-semibold">
                                        {{ mb_strtoupper(mb_substr($lead['name'] ?? '?', 0, 1)) }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <span class="flex items-center gap-[6px]">
                                            <span class="text-sm {{ $lead['is_read'] ? 'text-black dark:text-white' : 'font-semibold text-black dark:text-white' }} truncate">
                                                {{ $lead['name'] }}
                                            </span>
                                            @unless ($lead['is_read'])
                                                <span class="shrink-0 w-[7px] h-[7px] rounded-full bg-primary-500" title="Okunmadı"></span>
                                            @endunless
                                        </span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">{{ $lead['preview'] }}</span>
                                        <span class="block text-[11px] text-gray-400 mt-[2px]">{{ $lead['ago'] }} · {{ $lead['source_label'] }}</span>
                                    </div>
                                    <i class="material-symbols-outlined !text-[17px] text-gray-300 dark:text-gray-600 transition-all group-hover:text-primary-500">chevron_right</i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="py-[40px] text-center">
                        <i class="material-symbols-outlined !text-[34px] text-gray-300 dark:text-gray-600">inbox</i>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mt-[8px]">Henüz talep gelmedi.</span>
                    </div>
                @endif
            </div>
        @endcan

        @can('analytics.data')
            <div class="{{ $card }}" @if ($analyticsReady) data-top-pages @endif>
                <div class="trezo-card-header mb-[18px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">En Çok Görüntülenen</h5>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Seçili aralıkta</span>
                    </div>
                </div>

                @if ($analyticsReady)
                    {{-- Trafik kartıyla AYNI isteği paylaşır; aralık değişince birlikte güncellenir. --}}
                    <div data-top-pages-list class="text-sm text-gray-400 py-[20px] text-center">Yükleniyor…</div>
                @else
                    <div class="py-[40px] text-center">
                        <i class="material-symbols-outlined !text-[34px] text-gray-300 dark:text-gray-600">bar_chart</i>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mt-[8px]">GA4 bağlantısı kurulunca dolar.</span>
                    </div>
                @endif
            </div>
        @endcan

        @can('activity-log.index')
            <div class="{{ $card }}">
                <div class="trezo-card-header mb-[18px] flex items-center justify-between gap-[10px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Son Etkinlikler</h5>
                    </div>
                    <a href="{{ route('admin.activity-log.index') }}" class="text-xs text-primary-500 hover:underline whitespace-nowrap">Tümü</a>
                </div>

                @if ($activity)
                    <ul class="flex flex-col">
                        @foreach ($activity as $log)
                            <li class="flex items-start gap-[10px] py-[9px] border-b border-gray-100 dark:border-[#172036] last:border-0">
                                <span class="shrink-0 w-[28px] h-[28px] rounded-md bg-gray-50 dark:bg-[#15203c] text-gray-500 dark:text-gray-400 flex items-center justify-center">
                                    <i class="material-symbols-outlined !text-[15px]">{{ $log['module']['icon'] ?? 'bolt' }}</i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <span class="block text-xs text-black dark:text-white leading-[1.5]">{{ $log['description'] }}</span>
                                    <span class="block text-[11px] text-gray-400 mt-[2px]">
                                        {{ $log['causer'] }} · {{ $log['ago'] }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="py-[40px] text-center">
                        <i class="material-symbols-outlined !text-[34px] text-gray-300 dark:text-gray-600">history</i>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mt-[8px]">Henüz kayıt yok.</span>
                    </div>
                @endif
            </div>
        @endcan
    </div>

    {{-- ── SEO'da dikkat isteyenler ────────────────────────────────── --}}
    @can('seo.index')
        @if ($seoWeakest)
            <div class="{{ $card }} mb-[25px]">
                <div class="trezo-card-header mb-[18px] flex items-center justify-between gap-[10px] flex-wrap">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">SEO'da Dikkat İsteyen İçerikler</h5>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            En düşük skorlu 5 kayıt. Skoru olmayanlar burada yok — onlar kötü değil, henüz ölçülmedi.
                        </span>
                    </div>
                    <a href="{{ route('admin.seo.index') }}"
                        class="inline-flex items-center gap-[5px] text-sm text-primary-500 hover:underline">
                        SEO Sağlığı <i class="material-symbols-outlined !text-[16px]">arrow_forward</i>
                    </a>
                </div>

                <div class="table-responsive overflow-x-auto">
                    <table class="w-full">
                        <thead class="text-black dark:text-white">
                            <tr>
                                <th class="{{ $th }} first:rounded-tl-md">İçerik</th>
                                <th class="{{ $th }}">Odak Kelime</th>
                                <th class="{{ $th }}">Skor</th>
                                <th class="{{ $th }} last:rounded-tr-md"></th>
                            </tr>
                        </thead>
                        <tbody class="text-black dark:text-white">
                            @foreach ($seoWeakest as $item)
                                @php
                                    $bar = $item['score'] <= 40 ? 'bg-danger-500' : ($item['score'] <= 70 ? 'bg-warning-500' : 'bg-success-500');
                                @endphp
                                <tr>
                                    <td class="{{ $td }}">
                                        <span class="block truncate max-w-[360px] font-medium">{{ $item['title'] }}</span>
                                    </td>
                                    <td class="{{ $td }}">
                                        @if ($item['focus_keyword'])
                                            <code class="text-xs">{{ $item['focus_keyword'] }}</code>
                                        @else
                                            <span class="text-xs text-warning-600">girilmemiş</span>
                                        @endif
                                    </td>
                                    <td class="{{ $td }}">
                                        <span class="flex items-center gap-[8px]">
                                            <span class="block w-[70px] h-[5px] rounded-full bg-gray-100 dark:bg-[#15203c] overflow-hidden">
                                                <span class="block h-full rounded-full {{ $bar }}" style="width: {{ $item['score'] }}%"></span>
                                            </span>
                                            <span class="text-xs font-semibold">{{ $item['score'] }}</span>
                                        </span>
                                    </td>
                                    <td class="{{ $td }}">
                                        <a href="{{ $item['url'] }}" class="text-primary-500 text-xs hover:underline whitespace-nowrap">
                                            Düzelt
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endcan

    {{-- ── Kısayollar ──────────────────────────────────────────────── --}}
    <div class="{{ $card }}">
        <div class="trezo-card-header mb-[18px]">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Kısayollar</h5>
            </div>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-8 gap-[12px]">
            @php
                $shortcuts = [
                    ['Yeni Yazı', 'post_add', 'admin.blog.create', 'blog.create'],
                    ['Yeni Proje', 'workspaces', 'admin.project.create', 'project.create'],
                    ['Yeni Sayfa', 'note_add', 'admin.page.create', 'page.create'],
                    ['Menüler', 'menu', 'admin.menu.index', 'menu.index'],
                    ['Site Haritası', 'account_tree', 'admin.sitemap.index', 'sitemap.index'],
                    ['Revizyonlar', 'history', 'admin.revision.index', 'revision.index'],
                    ['Sistem Sağlığı', 'monitor_heart', 'admin.health.index', 'health.index'],
                    ['Ayarlar', 'settings', 'admin.setting.index', 'setting.index'],
                ];
            @endphp
            @foreach ($shortcuts as [$label, $icon, $route, $permission])
                @can($permission)
                    <a href="{{ route($route) }}"
                        class="p-[14px] rounded-md border border-gray-100 dark:border-[#172036] flex flex-col items-center gap-[8px] text-center transition-all hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined text-primary-500">{{ $icon }}</i>
                        <span class="text-xs font-medium text-black dark:text-white leading-[1.3]">{{ $label }}</span>
                    </a>
                @endcan
            @endforeach
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/dashboard/index.js') }}"></script>
@endpush
