@extends('admin.layout.app')
@section('admin.title', 'Hizmetler')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Hizmetler</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Hizmetler
            </li>
        </ol>
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-center sm:justify-between">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Hizmetler</h5>
            </div>
            <div class="trezo-card-subtitle mt-[15px] sm:mt-0 flex items-center gap-[10px] flex-wrap">
                <div class="relative grow max-w-[240px]">
                    <input type="text" id="service-search" placeholder="Ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>

                <select id="service-region" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm bölgeler</option>
                    {{-- $regions artık App\Support\Tree::options() çıktısı — bir ilin
                         altındaki ilçe soldan girintili, ikonlu görünür. --}}
                    @foreach ($regions as $id => $region)
                        @php($rendered = \App\Support\Tree::render($region['label'], $region['depth']))
                        <option value="{{ $id }}"
                            @if ($rendered['customProperties']) data-custom-properties="{{ json_encode($rendered['customProperties']) }}" @endif>
                            {{ $rendered['display'] }}
                        </option>
                    @endforeach
                </select>

                <select id="service-status" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm durumlar</option>
                    @foreach (\App\Models\Service\Service::STATUSES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                    @can('service.reorder')
                    <button type="button" id="service-reorder"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[19px]">drag_indicator</i>
                        Sıralama Modu
                    </button>
                @endcan

                <x-admin::activity-log-button module="service" />
                @can('service.create')
                    <a href="{{ route('admin.service.create') }}"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                        <i class="material-symbols-outlined !text-[19px]">add</i>
                        Yeni Hizmet
                    </a>
                @endcan
            </div>
        </div>

        <div class="trezo-card-content">
            <div class="table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            {{-- Sıralama modunda görünür; core/table.js açar/kapar. --}}
                            <th data-reorder-column
                                class="hidden font-medium px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap w-[36px] first:rounded-tl-md">
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md w-[70px]">
                                Görsel
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="title">
                                Başlık <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">
                                Bölge Sayısı
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="status">
                                Durum <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="created_at">
                                Tarih <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">
                                İşlemler
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" id="service-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/service/index.js') }}"></script>
@endpush
