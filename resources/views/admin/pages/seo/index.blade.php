@extends('admin.layout.app')
@section('admin.title', 'SEO Sağlığı')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">SEO Sağlığı</h5>
        <div class="flex items-center gap-[12px] mt-[12px] md:mt-0">
            @can('seo.rescore')
                <button type="button" id="seo-rescore"
                    class="inline-flex items-center gap-[6px] py-[8px] px-[16px] text-sm text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="material-symbols-outlined !text-[18px]">refresh</i>
                    Tümünü yeniden puanla
                </button>
            @endcan
            <ol class="breadcrumb">
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                    <a href="{{ route('admin.dashboard') }}" class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                        <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                        Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">SEO Sağlığı</li>
            </ol>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[15px] mb-[25px]" data-seo-overview
        data-overview="{{ json_encode($overview) }}">
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md flex items-center gap-[14px]">
            <div class="relative shrink-0 w-[56px] h-[56px]">
                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90 text-gray-100 dark:text-[#172036]">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3"></circle>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke-width="3" stroke-linecap="round" data-ov-ring stroke-dasharray="0 100"></circle>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-black dark:text-white" data-ov-average>–</span>
            </div>
            <div>
                <span class="block text-xs text-gray-500 dark:text-gray-400">Ortalama skor</span>
                <span class="block text-sm text-black dark:text-white" data-ov-analyzed></span>
            </div>
        </div>
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md">
            <span class="block text-xl font-bold text-success-600" data-ov-good>0</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">İyi (71-100)</span>
        </div>
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md">
            <span class="block text-xl font-bold text-warning-600" data-ov-ok>0</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">İyileştirilebilir (41-70)</span>
        </div>
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md">
            <span class="block text-xl font-bold text-danger-500" data-ov-bad>0</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">Kötü / analiz yok</span>
        </div>
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md"
        data-seo-health data-endpoint="{{ route('admin.seo.datatable') }}">
        <div class="flex flex-wrap gap-[8px] mb-[20px] border-b border-gray-100 dark:border-[#172036] pb-[15px]" data-tabs>
            @foreach ($tabs as $key => $label)
                <button type="button" data-tab="{{ $key }}"
                    class="py-[7px] px-[14px] text-sm rounded-md transition-all">
                    {{ $label }}
                    <span class="text-xs opacity-70" data-tab-count="{{ $key }}">({{ $overview['tabs'][$key] ?? 0 }})</span>
                </button>
            @endforeach
        </div>

        <div class="table-responsive overflow-x-auto">
            <table class="w-full">
                <thead class="text-black dark:text-white">
                    <tr>
                        <th class="font-medium ltr:text-left rtl:text-right px-[16px] py-[10px] bg-gray-50 dark:bg-[#15203c] first:rounded-tl-md text-sm">İçerik</th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[16px] py-[10px] bg-gray-50 dark:bg-[#15203c] text-sm">Tür</th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[16px] py-[10px] bg-gray-50 dark:bg-[#15203c] text-sm">Sorun</th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[16px] py-[10px] bg-gray-50 dark:bg-[#15203c] text-sm">Skor</th>
                        <th class="font-medium px-[16px] py-[10px] bg-gray-50 dark:bg-[#15203c] last:rounded-tr-md"></th>
                    </tr>
                </thead>
                <tbody id="seo-health-body" class="text-black dark:text-white"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/seo/index.js') }}"></script>
@endpush
