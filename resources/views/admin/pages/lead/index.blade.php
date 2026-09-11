@extends('admin.layout.app')
@section('admin.title', 'Gelen Talepler')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">Gelen Talepler</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Gelen Talepler</li>
        </ol>
    </div>

    {{-- Özet kartları: tıklanınca o duruma filtreler --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-[15px] mb-[25px]" data-lead-stats>
        <button type="button" data-stat-filter="" data-stat-unread
            class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md text-left transition-all hover:shadow-md">
            <span class="flex items-center gap-[6px] text-[11px] text-gray-500 dark:text-gray-400 mb-[6px]">
                <i class="material-symbols-outlined !text-[15px] text-primary-500">mark_email_unread</i> Okunmamış
            </span>
            <span class="block text-lg font-bold text-black dark:text-white leading-none">{{ $stats['unread'] }}</span>
        </button>

        @foreach ($stats['statuses'] as $status)
            <button type="button" data-stat-filter="{{ $status['key'] }}"
                class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md text-left transition-all hover:shadow-md">
                <span class="flex items-center gap-[6px] text-[11px] text-gray-500 dark:text-gray-400 mb-[6px]">
                    <i class="material-symbols-outlined !text-[15px] text-{{ $status['color'] }}-500">{{ $status['icon'] }}</i>
                    {{ $status['label'] }}
                </span>
                <span class="block text-lg font-bold text-black dark:text-white leading-none" data-stat-count="{{ $status['key'] }}">{{ $status['total'] }}</span>
            </button>
        @endforeach

        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[16px] rounded-md">
            <span class="flex items-center gap-[6px] text-[11px] text-gray-500 dark:text-gray-400 mb-[6px]">
                <i class="material-symbols-outlined !text-[15px] text-info-500">today</i> Bugün
            </span>
            <span class="block text-lg font-bold text-black dark:text-white leading-none" data-stat-today>{{ $stats['today'] }}</span>
        </div>
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md"
        data-lead-table
        data-endpoint="{{ route('admin.lead.datatable') }}"
        data-stats-endpoint="{{ route('admin.lead.stats') }}"
        data-bulk-endpoint="{{ route('admin.lead.bulk') }}"
        data-export-endpoint="{{ route('admin.lead.export') }}">

        {{-- Filtreler --}}
        <div class="trezo-card-header mb-[20px] flex flex-wrap items-end gap-[12px] justify-between">
            <div class="flex flex-wrap items-end gap-[12px]">
                <div class="relative">
                    <input type="text" id="lead-search" placeholder="Ad, e-posta, telefon, mesaj…"
                        class="h-[38px] w-[240px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] ltr:pl-[36px] rtl:pr-[36px] ltr:pr-[14px] rtl:pl-[14px] outline-0 transition-all placeholder:text-gray-500 focus:border-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-[10px] rtl:right-[10px] top-1/2 -translate-y-1/2 !text-[19px] text-gray-400">search</i>
                </div>

                <select id="lead-status" data-choices
                    class="h-[38px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] cursor-pointer outline-0 focus:border-primary-500">
                    <option value="">Tüm durumlar</option>
                    @foreach (config('leads.statuses') as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>

                <select id="lead-assigned" data-choices
                    class="h-[38px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] cursor-pointer outline-0 focus:border-primary-500">
                    <option value="">Herkes</option>
                    <option value="none">Atanmamış</option>
                    @foreach ($assignees as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>

                {{-- Filtre tarihleri: bileşen yerine ham input — core/datepicker.js data-datepicker'ı kendisi kurar. --}}
                <input type="text" id="lead-from" data-datepicker data-datepicker-time="0" placeholder="Başlangıç"
                    class="h-[38px] w-[140px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] outline-0 transition-all placeholder:text-gray-500 focus:border-primary-500">
                <input type="text" id="lead-to" data-datepicker data-datepicker-time="0" placeholder="Bitiş"
                    class="h-[38px] w-[140px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] outline-0 transition-all placeholder:text-gray-500 focus:border-primary-500">
            </div>

            <div class="flex flex-wrap items-center gap-[10px]">
                <label class="inline-flex items-center gap-[6px] text-sm text-black dark:text-white cursor-pointer select-none">
                    <input type="checkbox" id="lead-unread" class="w-[16px] h-[16px] accent-primary-500 cursor-pointer">
                    Sadece okunmamış
                </label>
                <label class="inline-flex items-center gap-[6px] text-sm text-black dark:text-white cursor-pointer select-none">
                    <input type="checkbox" id="lead-trashed" class="w-[16px] h-[16px] accent-primary-500 cursor-pointer">
                    Çöp kutusu <span class="text-gray-400" data-stat-trashed>({{ $stats['trashed'] }})</span>
                </label>
                @can('lead.export')
                    <button type="button" id="lead-export"
                        class="inline-flex items-center gap-[6px] py-[8px] px-[14px] text-sm text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[17px]">download</i> CSV
                    </button>
                @endcan
            </div>
        </div>

        {{-- Toplu işlem çubuğu: seçim yapılınca görünür --}}
        <div class="hidden flex-wrap items-center gap-[10px] p-[12px] mb-[15px] rounded-md bg-primary-50 dark:bg-[#15203c] border border-primary-100 dark:border-[#172036]"
            data-bulk-bar>
            <span class="text-sm text-black dark:text-white">
                <strong data-bulk-count>0</strong> kayıt seçili
            </span>
            <span class="flex-1"></span>
            @can('lead.bulk')
                <button type="button" data-bulk="read"
                    class="inline-flex items-center gap-[5px] py-[6px] px-[12px] text-xs text-black dark:text-white rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-gray-50 dark:hover:bg-[#172036] transition-all">
                    <i class="material-symbols-outlined !text-[15px]">mark_email_read</i> Okundu
                </button>
                <button type="button" data-bulk="unread"
                    class="inline-flex items-center gap-[5px] py-[6px] px-[12px] text-xs text-black dark:text-white rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-gray-50 dark:hover:bg-[#172036] transition-all">
                    <i class="material-symbols-outlined !text-[15px]">mark_email_unread</i> Okunmadı
                </button>
                <select data-bulk-status
                    class="h-[30px] rounded-md text-xs text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[8px] cursor-pointer outline-0">
                    <option value="">Durum ata…</option>
                    @foreach (config('leads.statuses') as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <button type="button" data-bulk="delete"
                    class="inline-flex items-center gap-[5px] py-[6px] px-[12px] text-xs text-white rounded-md bg-danger-500 hover:bg-danger-400 border border-danger-500 hover:border-danger-400 transition-all">
                    <i class="material-symbols-outlined !text-[15px]">delete</i> Sil
                </button>
                <button type="button" data-bulk="restore" data-restore-only
                    class="hidden items-center gap-[5px] py-[6px] px-[12px] text-xs text-white rounded-md bg-success-500 hover:bg-success-400 border border-success-500 hover:border-success-400 transition-all">
                    <i class="material-symbols-outlined !text-[15px]">restore_from_trash</i> Geri al
                </button>
            @endcan
        </div>

        <div class="table-responsive overflow-x-auto">
            <table class="w-full">
                <thead class="text-black dark:text-white">
                    <tr>
                        <th class="font-medium px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] w-[40px] first:rounded-tl-md">
                            <input type="checkbox" data-select-all class="w-[16px] h-[16px] accent-primary-500 cursor-pointer">
                        </th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer" data-column="name">
                            Gönderen <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                        </th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c]">Mesaj</th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer" data-column="status">
                            Durum <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                        </th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer" data-column="created_at">
                            Tarih <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                        </th>
                        <th class="font-medium px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] last:rounded-tr-md"></th>
                    </tr>
                </thead>
                <tbody id="lead-table-body" class="text-black dark:text-white"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/lead/index.js') }}"></script>
@endpush
