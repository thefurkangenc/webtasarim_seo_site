@extends('admin.layout.app')
@section('admin.title', 'Kullanıcılar')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Kullanıcılar</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Kullanıcılar
            </li>
        </ol>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-[15px] md:gap-[25px] mb-[25px]">
        @php
            $cards = [
                ['Toplam', $stats['total'], 'group', 'text-primary-500', 'bg-primary-50'],
                ['Aktif', $stats['active'], 'check_circle', 'text-success-600', 'bg-success-50'],
                ['Pasif', $stats['inactive'], 'block', 'text-danger-500', 'bg-danger-50'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $icon, $text, $bg])
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] rounded-md flex items-center gap-[14px]">
                <span class="shrink-0 w-[42px] h-[42px] rounded-[12px] {{ $bg }} dark:bg-[#15203c] {{ $text }} flex items-center justify-center">
                    <i class="material-symbols-outlined !text-[21px]">{{ $icon }}</i>
                </span>
                <div class="min-w-0">
                    <span class="block text-[22px] font-bold text-black dark:text-white leading-none mb-[4px]">{{ $value }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header mb-[20px] md:mb-[25px]">
            <div class="sm:flex sm:items-start sm:justify-between gap-[15px]">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">Kullanıcılar</h5>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Panele giriş yapabilecek hesaplar. Süper yönetici buradan silinemez ve rolü değiştirilemez.
                    </span>
                </div>
                <div class="trezo-card-subtitle mt-[15px] sm:mt-0 flex items-center gap-[10px] flex-wrap shrink-0">
                    <x-admin::activity-log-button module="user" />
                    @can('user.create')
                        <a href="{{ route('admin.user.create') }}"
                            class="inline-flex items-center gap-[6px] py-[9px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                            <i class="material-symbols-outlined !text-[19px]">add</i>
                            Yeni Kullanıcı
                        </a>
                    @endcan
                </div>
            </div>

            <div class="flex items-center gap-[10px] flex-wrap mt-[15px] md:mt-[20px]">
                <div class="relative grow max-w-[240px]">
                    <input type="text" id="user-search" placeholder="Ad, e-posta veya telefon ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>

                <select id="user-role" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm roller</option>
                    @foreach ($roles as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="user-status" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm durumlar</option>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                </select>
            </div>
        </div>

        <div class="trezo-card-content">
            <div class="table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md cursor-pointer relative" data-column="name">
                                Kullanıcı <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="email">
                                E-posta <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">
                                Telefon
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">
                                Rol
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="is_active">
                                Durum <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap cursor-pointer relative" data-column="last_login_at">
                                Son giriş <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                            </th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">
                                İşlemler
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" id="user-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/user/index.js') }}"></script>
@endpush
