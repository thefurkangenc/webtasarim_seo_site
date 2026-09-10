@extends('admin.layout.app')
@section('admin.title', 'Dashboard')

@section('content')
    <div class="mb-[25px]">
        <h5 class="!mb-[4px]">Merhaba{{ auth()->user()?->name ? ', '.auth()->user()->name : '' }} 👋</h5>
        <p class="text-sm text-gray-500 dark:text-gray-400">Sitenin güncel trafik özeti aşağıda.</p>
    </div>

    @can('analytics.data')
        @if ($analyticsReady)
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md"
                data-dashboard-analytics data-endpoint="{{ route('admin.analytics.data') }}">
                <div class="trezo-card-header mb-[20px] flex items-center justify-between gap-[12px] flex-wrap">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Site Trafiği</h5>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Google Analytics 4 · son 28 gün</span>
                    </div>
                    <a href="{{ route('admin.analytics.index') }}"
                        class="inline-flex items-center gap-[5px] text-sm text-primary-500 hover:underline">
                        Detaylı analitik
                        <i class="material-symbols-outlined !text-[17px]">arrow_forward</i>
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-[15px] mb-[20px]" data-kpis>
                    @for ($i = 0; $i < 4; $i++)
                        <div class="p-[14px] rounded-md bg-gray-50 dark:bg-[#15203c]">
                            <span class="block h-[10px] w-[55%] bg-gray-200 dark:bg-[#172036] rounded animate-pulse mb-[10px]"></span>
                            <span class="block h-[16px] w-[35%] bg-gray-200 dark:bg-[#172036] rounded animate-pulse"></span>
                        </div>
                    @endfor
                </div>

                <div id="dashboard-trend" class="min-h-[260px]"></div>
            </div>
        @else
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[25px] rounded-md flex items-center gap-[16px] flex-wrap">
                <i class="material-symbols-outlined !text-[32px] text-gray-300 dark:text-gray-600">insights</i>
                <div class="flex-1 min-w-[220px]">
                    <span class="block font-medium text-black dark:text-white">Trafik verisi için GA4 bağlantısı kurun</span>
                    <span class="block text-sm text-gray-500 dark:text-gray-400">Service account JSON + property ID ile birkaç dakikada.</span>
                </div>
                <a href="{{ route('admin.setting.edit', 'analytics') }}"
                    class="inline-flex items-center gap-[6px] py-[9px] px-[18px] bg-primary-500 text-white rounded-md hover:bg-primary-400 transition-all text-sm">
                    <i class="material-symbols-outlined !text-[18px]">settings</i> Bağlantıyı kur
                </a>
            </div>
        @endif
    @endcan

    @can('seo.index')
        @php
            $seoAvg = $seoOverview['average'] ?? 0;
            $seoRingClass = $seoAvg <= 40 ? 'text-danger-500' : ($seoAvg <= 70 ? 'text-warning-500' : 'text-success-500');
        @endphp
        <a href="{{ route('admin.seo.index') }}"
            class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md flex items-center gap-[18px] flex-wrap transition-all hover:border-primary-500 border border-transparent">
            <div class="relative shrink-0 w-[56px] h-[56px]">
                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90 text-gray-100 dark:text-[#172036]">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3"></circle>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke-width="3" stroke-linecap="round"
                        stroke-dasharray="{{ $seoOverview['average'] ?? 0 }} 100"
                        class="{{ $seoRingClass }}" stroke="currentColor"></circle>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-black dark:text-white">{{ $seoOverview['average'] ?? '–' }}</span>
            </div>
            <div class="flex-1 min-w-[180px]">
                <span class="block font-medium text-black dark:text-white">SEO Sağlığı</span>
                <span class="block text-sm text-gray-500 dark:text-gray-400">
                    Ortalama skor · {{ $seoOverview['analyzed'] }}/{{ $seoOverview['total'] }} içerik analizli
                </span>
            </div>
            <div class="flex gap-[16px] text-center">
                <div><span class="block text-lg font-bold text-success-600">{{ $seoOverview['distribution']['good'] }}</span><span class="block text-[11px] text-gray-500 dark:text-gray-400">iyi</span></div>
                <div><span class="block text-lg font-bold text-warning-600">{{ $seoOverview['distribution']['ok'] }}</span><span class="block text-[11px] text-gray-500 dark:text-gray-400">orta</span></div>
                <div><span class="block text-lg font-bold text-danger-500">{{ $seoOverview['distribution']['bad'] + $seoOverview['distribution']['none'] }}</span><span class="block text-[11px] text-gray-500 dark:text-gray-400">kötü</span></div>
            </div>
            <i class="material-symbols-outlined text-gray-400">arrow_forward</i>
        </a>
    @endcan

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-[15px]">
        @php
            $shortcuts = [
                ['Sayfalar', 'description', 'admin.page.index', 'page.index'],
                ['Blog', 'article', 'admin.blog.index', 'blog.index'],
                ['Hizmetler', 'design_services', 'admin.service.index', 'service.index'],
                ['Menüler', 'menu', 'admin.menu.index', 'menu.index'],
                ['Medya', 'perm_media', 'admin.media.index', 'media.index'],
                ['Ayarlar', 'settings', 'admin.setting.index', 'setting.index'],
            ];
        @endphp
        @foreach ($shortcuts as [$label, $icon, $route, $permission])
            @can($permission)
                <a href="{{ route($route) }}"
                    class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md flex flex-col items-center gap-[8px] text-center transition-all hover:border-primary-500 border border-transparent">
                    <i class="material-symbols-outlined text-primary-500">{{ $icon }}</i>
                    <span class="text-xs font-medium text-black dark:text-white">{{ $label }}</span>
                </a>
            @endcan
        @endforeach
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/dashboard/index.js') }}"></script>
@endpush
