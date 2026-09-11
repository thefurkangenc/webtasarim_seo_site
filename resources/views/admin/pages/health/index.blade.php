@extends('admin.layout.app')
@section('admin.title', 'Sistem Sağlığı')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Sistem Sağlığı</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Sistem Sağlığı</li>
        </ol>
    </div>

    <div data-health>
        {{-- Genel durum — halka, sayaçlar ve kritik sorun şeridi JS ile dolar. --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="lg:flex lg:items-center lg:justify-between gap-[25px]">

                <div class="flex items-center gap-[18px] min-w-0">
                    <div class="relative shrink-0 w-[84px] h-[84px]">
                        <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90 text-gray-100 dark:text-[#172036]">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3"></circle>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke-width="3" stroke-linecap="round"
                                data-summary-ring stroke-dasharray="0 100" class="transition-all duration-700"></circle>
                        </svg>
                        <span class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-lg font-bold text-black dark:text-white leading-none" data-summary-score>–</span>
                            <span class="text-[9px] text-gray-400 uppercase tracking-[.5px] mt-[2px]">sağlık</span>
                        </span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-[8px] flex-wrap">
                            <span data-summary-chip
                                class="shrink-0 w-[26px] h-[26px] rounded-full flex items-center justify-center bg-gray-100 dark:bg-[#15203c] text-gray-500 dark:text-gray-400">
                                <i class="material-symbols-outlined !text-[16px]" data-summary-icon>monitor_heart</i>
                            </span>
                            <h5 class="!mb-0" data-summary-title>Kontroller yükleniyor…</h5>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-[6px]" data-summary-note>
                            Kuyruk, zamanlanmış görevler, disk, veritabanı, sertifika ve dış servisler denetleniyor.
                        </p>
                    </div>
                </div>

                {{-- Sayaçlar --}}
                <div class="grid grid-cols-4 gap-[10px] mt-[20px] lg:mt-0 shrink-0">
                    @foreach ([
                        ['critical', 'Kritik', 'text-danger-500'],
                        ['warning', 'Uyarı', 'text-warning-600'],
                        ['ok', 'Sorunsuz', 'text-success-600'],
                        ['skipped', 'Kurulu değil', 'text-gray-400'],
                    ] as [$key, $label, $color])
                        <div class="text-center px-[14px] py-[10px] rounded-md bg-gray-50 dark:bg-[#15203c] min-w-[78px]">
                            <span class="block text-xl font-bold {{ $color }} leading-none" data-count="{{ $key }}">–</span>
                            <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-[4px]">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-[20px] lg:mt-0 flex items-center gap-[10px] flex-wrap shrink-0">
                    <x-admin::activity-log-button module="health" />

                    @can('health.data')
                        <button type="button" data-refresh
                            class="inline-flex items-center gap-[6px] py-[9px] px-[18px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                            <i class="material-symbols-outlined !text-[19px]">refresh</i> Yeniden Tara
                        </button>
                    @endcan
                </div>
            </div>

            {{-- Kritik sorun özeti: sorun yoksa gizli kalır. --}}
            <div class="hidden mt-[20px] p-[14px] rounded-md bg-danger-50 dark:bg-[#15203c] border border-danger-100 dark:border-[#172036]"
                data-critical-strip>
                <span class="flex items-center gap-[8px] text-sm font-medium text-danger-500 mb-[8px]">
                    <i class="material-symbols-outlined !text-[18px]">priority_high</i>
                    Önce bunları çözün
                </span>
                <ul class="space-y-[6px] text-xs text-gray-600 dark:text-gray-300" data-critical-list></ul>
            </div>

            <p class="text-[11px] text-gray-400 mt-[16px]">
                Rapor saatte bir kendiliğinden tazelenir (sunucudaki zamanlayıcı ile); “Yeniden Tara”
                tüm kontrolleri o anda baştan çalıştırır.
            </p>
        </div>

        {{-- Kontroller gruplar halinde; içerik JS ile basılır. --}}
        <div class="mb-[25px]" data-checks
            data-groups="{{ json_encode(config('health.groups'), JSON_UNESCAPED_UNICODE) }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-[15px] md:gap-[25px]">
                @for ($i = 0; $i < 4; $i++)
                    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md">
                        <span class="block h-[12px] w-[40%] bg-gray-100 dark:bg-[#15203c] rounded animate-pulse mb-[10px]"></span>
                        <span class="block h-[10px] w-[75%] bg-gray-100 dark:bg-[#15203c] rounded animate-pulse"></span>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Başarısız işler. Liste boşsa JS bu kartı gizler. --}}
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md hidden" data-failed-card>
            <div class="trezo-card-header mb-[20px] sm:flex sm:items-start sm:justify-between">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">Başarısız İşler</h5>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-[4px] max-w-[560px]">
                        Kuyrukta hata alıp duran işler. Sebebi giderdikten sonra “yeniden dene” ile
                        aynı iş baştan çalıştırılır; artık gereksizse kaydı silin.
                    </p>
                </div>

                <div class="trezo-card-subtitle mt-[15px] sm:mt-0 flex items-center gap-[10px] flex-wrap shrink-0">
                    @can('health.retry-all')
                        <button type="button" data-retry-all
                            class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                            <i class="material-symbols-outlined !text-[18px]">restart_alt</i> Tümünü Yeniden Dene
                        </button>
                    @endcan

                    @can('health.flush')
                        <button type="button" data-flush
                            class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-danger-500 transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                            <i class="material-symbols-outlined !text-[18px]">delete_sweep</i> Listeyi Temizle
                        </button>
                    @endcan
                </div>
            </div>

            <div class="trezo-card-content table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md">İş</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Hata</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Zaman</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" data-failed-body></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/health/index.js') }}"></script>
@endpush
