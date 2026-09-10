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
