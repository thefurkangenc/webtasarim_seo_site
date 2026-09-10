@extends('admin.layout.app')
@section('admin.title', 'Analitik')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">Analitik <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Google Analytics 4)</span></h5>
        <div class="flex items-center gap-[12px] mt-[12px] md:mt-0">
            <a href="{{ route('admin.setting.edit', 'analytics') }}"
                class="inline-flex items-center gap-[6px] py-[8px] px-[16px] text-sm text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[18px]">tune</i>
                Bağlantı ayarları
            </a>
            <ol class="breadcrumb">
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                    <a href="{{ route('admin.dashboard') }}" class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                        <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                        Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Analitik</li>
            </ol>
        </div>
    </div>

    @unless ($configured)
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[40px] rounded-md text-center">
            <i class="material-symbols-outlined !text-[48px] text-gray-300 dark:text-gray-600">insights</i>
            <h5 class="!mt-[16px] !mb-[8px]">GA4 bağlantısı yok</h5>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-[420px] mx-auto mb-[20px]">
                Panelde canlı ziyaretçi verisini görmek için bir Google service account JSON dosyası
                ve GA4 property ID girin.
            </p>
            <a href="{{ route('admin.setting.edit', 'analytics') }}"
                class="inline-flex items-center gap-[6px] py-[10px] px-[22px] bg-primary-500 text-white rounded-md hover:bg-primary-400 transition-all">
                <i class="material-symbols-outlined !text-[19px]">settings</i>
                Bağlantıyı kur
            </a>
        </div>
    @else
        <div data-analytics
            data-endpoint="{{ route('admin.analytics.data') }}"
            data-realtime="{{ route('admin.analytics.realtime') }}">

            {{-- Canlı --}}
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="flex flex-wrap items-center gap-x-[24px] gap-y-[14px]">
                    <div class="flex items-center gap-[12px]">
                        <span class="relative flex h-[10px] w-[10px]">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-[10px] w-[10px] bg-success-500"></span>
                        </span>
                        <div>
                            <span class="block text-2xl font-bold text-black dark:text-white leading-none" data-rt-users>–</span>
                            <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-[3px]">son 30 dk aktif kullanıcı</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <ul class="flex flex-wrap gap-x-[20px] gap-y-[4px] text-xs text-gray-600 dark:text-gray-300" data-rt-pages></ul>
                    </div>
                    <span class="text-[11px] text-gray-400" data-rt-time></span>
                </div>
            </div>

            {{-- Tarih aralığı --}}
            <div class="flex items-center justify-between flex-wrap gap-[12px] mb-[20px]">
                <div class="inline-flex rounded-md border border-gray-200 dark:border-[#172036] overflow-hidden" data-range>
                    <button type="button" data-days="7" class="py-[7px] px-[16px] text-sm transition-all">7 gün</button>
                    <button type="button" data-days="28" class="py-[7px] px-[16px] text-sm transition-all border-x border-gray-200 dark:border-[#172036]">28 gün</button>
                    <button type="button" data-days="90" class="py-[7px] px-[16px] text-sm transition-all">90 gün</button>
                </div>
                <span class="text-xs text-gray-400" data-updated></span>
            </div>

            {{-- KPI kartları --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-[15px] mb-[25px]" data-kpis>
                @for ($i = 0; $i < 6; $i++)
                    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md">
                        <span class="block h-[10px] w-[60%] bg-gray-100 dark:bg-[#172036] rounded animate-pulse mb-[10px]"></span>
                        <span class="block h-[18px] w-[40%] bg-gray-100 dark:bg-[#172036] rounded animate-pulse"></span>
                    </div>
                @endfor
            </div>

            {{-- Eğilim grafiği --}}
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[10px]">
                    <div class="trezo-card-title"><h5 class="!mb-0">Oturum &amp; kullanıcı eğilimi</h5></div>
                </div>
                <div id="analytics-trend" class="min-h-[300px]"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-[25px] mb-[25px]">
                {{-- En çok görüntülenen sayfalar --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[15px]">
                        <div class="trezo-card-title"><h5 class="!mb-0">En çok görüntülenen sayfalar</h5></div>
                    </div>
                    <div class="table-responsive overflow-x-auto">
                        <table class="w-full text-sm">
                            <tbody data-top-pages class="text-black dark:text-white"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Kanallar --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[10px]">
                        <div class="trezo-card-title"><h5 class="!mb-0">Trafik kanalları</h5></div>
                    </div>
                    <div id="analytics-channels" class="min-h-[280px]"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-[25px]">
                {{-- Cihazlar --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[10px]">
                        <div class="trezo-card-title"><h5 class="!mb-0">Cihazlar</h5></div>
                    </div>
                    <div id="analytics-devices" class="min-h-[280px]"></div>
                </div>

                {{-- Ülkeler --}}
                <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[15px]">
                        <div class="trezo-card-title"><h5 class="!mb-0">Ülkeler</h5></div>
                    </div>
                    <ul data-countries class="space-y-[10px] text-sm text-black dark:text-white"></ul>
                </div>
            </div>
        </div>
    @endunless
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/analytics/index.js') }}"></script>
@endpush
