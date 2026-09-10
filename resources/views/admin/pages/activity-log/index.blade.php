@extends('admin.layout.app')
@section('admin.title', 'Log Kayıtları')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Log Kayıtları</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Log Kayıtları
            </li>
        </ol>
    </div>

    {{-- Özet kartlar — filtreden bağımsız, her zaman genel durumu gösterir --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-[25px] mb-[25px]">
        @php
            // Sınıf adları tam yazılı: Tailwind taraması statik, "bg-{$color}-100"
            // gibi birleştirilen adlar derlenmez.
            $cards = [
                ['Toplam Kayıt', number_format($stats['total']), 'history', 'bg-primary-100 dark:bg-[#15203c]', 'text-primary-500'],
                ['Bugün', number_format($stats['today']), 'today', 'bg-secondary-100 dark:bg-[#15203c]', 'text-secondary-500'],
                ['Kritik Olay', number_format($stats['critical']), 'gpp_maybe', 'bg-danger-100 dark:bg-[#15203c]', 'text-danger-500'],
                ['Başarısız Giriş (7 gün)', number_format($stats['failed_logins']), 'gpp_bad', 'bg-warning-100 dark:bg-[#15203c]', 'text-warning-500'],
            ];
        @endphp

        @foreach ($cards as [$label, $value, $icon, $chipClass, $iconClass])
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-content flex items-center gap-[15px]">
                    <span
                        class="w-[48px] h-[48px] rounded-[12px] shrink-0 flex items-center justify-center {{ $chipClass }}">
                        <i class="material-symbols-outlined !text-[24px] {{ $iconClass }}">{{ $icon }}</i>
                    </span>
                    <div class="min-w-0">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">{{ $label }}</span>
                        <span class="block text-xl font-bold text-black dark:text-white mt-[2px]">{{ $value }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-center sm:justify-between gap-[12px]">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Denetim Kaydı</h5>
            </div>
            <div class="trezo-card-subtitle mt-[15px] sm:mt-0">
                <div class="relative w-full sm:w-[260px]">
                    <input type="text" id="log-search" placeholder="Açıklama, kullanıcı, IP ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-[10px] w-full block text-black ltr:pl-[38px] rtl:pr-[38px] ltr:pr-[13px] rtl:pl-[13px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:left-[12px] rtl:right-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>
            </div>
        </div>

        {{-- Filtreler. Her birinin üstünde etiket var: altı filtre placeholder'a
             sığmıyordu ve tarih alanlarında bileşen kendi placeholder'ını
             sabit yazdığı için "Başlangıç/Bitiş" ayrımı görünmüyordu. --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-[12px] mb-[20px] md:mb-[25px]">
            <div>
                <label class="mb-[6px] text-xs font-medium text-gray-500 dark:text-gray-400 block">Modül</label>
                <select id="log-module" data-choices class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all">
                    <option value="">Tümü</option>
                    @foreach ($options['modules'] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-[6px] text-xs font-medium text-gray-500 dark:text-gray-400 block">Olay</label>
                <select id="log-event" data-choices class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all">
                    <option value="">Tümü</option>
                    @foreach ($options['events'] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-[6px] text-xs font-medium text-gray-500 dark:text-gray-400 block">Önem</label>
                <select id="log-severity" data-choices class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all">
                    <option value="">Tümü</option>
                    @foreach ($options['severities'] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-[6px] text-xs font-medium text-gray-500 dark:text-gray-400 block">Cihaz</label>
                <select id="log-device" data-choices class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer transition-all">
                    <option value="">Tümü</option>
                    @foreach ($options['devices'] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-[6px] text-xs font-medium text-gray-500 dark:text-gray-400 block">Başlangıç</label>
                <x-admin::form.date name="date_from" :time="false" wrapper="" />
            </div>
            <div>
                <label class="mb-[6px] text-xs font-medium text-gray-500 dark:text-gray-400 block">Bitiş</label>
                <x-admin::form.date name="date_to" :time="false" wrapper="" />
            </div>
        </div>

        <div class="trezo-card-content">
            <div class="table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] whitespace-nowrap cursor-pointer relative first:rounded-tl-md"
                                data-column="event">
                                Olay <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] whitespace-nowrap">
                                Kayıt
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] whitespace-nowrap">
                                Kullanıcı
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] whitespace-nowrap">
                                Cihaz / Konum
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] whitespace-nowrap cursor-pointer relative"
                                data-column="created_at">
                                Tarih <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] whitespace-nowrap last:rounded-tr-md">
                                Detay
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" id="log-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/activity-log/index.js') }}"></script>
@endpush
