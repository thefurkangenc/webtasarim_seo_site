@extends('admin.layout.app')
@section('admin.title', 'Yönlendirmeler')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Yönlendirmeler</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Yönlendirmeler
            </li>
        </ol>
    </div>

    {{-- Özet kartlar. Sınıf adları tam yazılı — Tailwind taraması statik. --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-[15px] md:gap-[25px] mb-[25px]" data-redirect-stats>
        @php
            $cards = [
                ['total', 'Toplam Yönlendirme', 'alt_route', 'bg-primary-100 dark:bg-[#15203c]', 'text-primary-500'],
                ['active', 'Aktif', 'toggle_on', 'bg-success-100 dark:bg-[#15203c]', 'text-success-600'],
                ['auto', 'Otomatik (slug değişimi)', 'bolt', 'bg-secondary-100 dark:bg-[#15203c]', 'text-secondary-500'],
                ['unresolved_404', 'Çözülmemiş 404', 'link_off', 'bg-warning-100 dark:bg-[#15203c]', 'text-warning-600'],
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

    {{-- Yönlendirmeler --}}
    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-center sm:justify-between">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Yönlendirmeler</h5>
            </div>
            <div class="trezo-card-subtitle mt-[15px] sm:mt-0 flex items-center gap-[10px] flex-wrap">
                <div class="relative grow max-w-[220px]">
                    <input type="text" id="redirect-search" placeholder="Ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>

                <select id="redirect-type" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm tipler</option>
                    @foreach (config('redirects.match_types') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="redirect-status" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm durumlar</option>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                </select>

                <a href="{{ route('admin.redirect.export') }}"
                    class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="material-symbols-outlined !text-[18px]">download</i> Dışa Aktar
                </a>

                @can('redirect.import')
                    <button type="button" id="redirect-import"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px]">upload</i> İçe Aktar
                    </button>
                    <input type="file" id="redirect-import-file" accept=".csv,text/csv" class="hidden">
                @endcan

                @can('redirect.store')
                    <button type="button" id="redirect-create"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                        <i class="material-symbols-outlined !text-[19px]">add</i> Yeni Yönlendirme
                    </button>
                @endcan
            </div>
        </div>

        <div class="trezo-card-content">
            <div class="table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md cursor-pointer relative" data-column="from_path">
                                Kaynak → Hedef <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Tip</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="status_code">
                                Kod <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="hits">
                                İsabet <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="last_hit_at">
                                Son İsabet <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Aktif</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" id="redirect-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 404 kayıtları --}}
    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-center sm:justify-between">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Bulunamayan Adresler (404)</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                    Ziyaretçilerin isteyip de bulamadığı adresler. Bir satırda “Yönlendir” ile hızlıca 301 oluşturabilirsiniz.
                </p>
            </div>
            <div class="trezo-card-subtitle mt-[15px] sm:mt-0 flex items-center gap-[10px] flex-wrap">
                <div class="relative grow max-w-[220px]">
                    <input type="text" id="nf-search" placeholder="Adres ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>

                <label class="flex items-center gap-[8px] cursor-pointer select-none text-sm text-black dark:text-white">
                    <input type="checkbox" id="nf-resolved" class="w-[16px] h-[16px] accent-primary-500">
                    Çözülmüşleri de göster
                </label>
            </div>
        </div>

        <div class="trezo-card-content">
            <div class="table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md cursor-pointer relative" data-column="path">
                                Adres <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="hits">
                                İsabet <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Referer</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="last_seen_at">
                                Son Görülme <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" id="nf-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.pages.redirect.modals.import-result')
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/redirect/index.js') }}"></script>
@endpush
