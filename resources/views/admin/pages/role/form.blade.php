@extends('admin.layout.app')
@section('admin.title', $role ? 'Rolü Düzenle' : 'Yeni Rol')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">{{ $role ? 'Rolü Düzenle' : 'Yeni Rol' }}</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.role.index') }}" class="transition-all hover:text-primary-500">Roller</a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                {{ $role ? 'Düzenle' : 'Yeni' }}
            </li>
        </ol>
    </div>

    <form id="role-form" data-id="{{ $role?->id }}">
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-content">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-[20px] md:gap-[25px]">
                    <x-admin::form.input name="name" help="role.name" label="Rol Adı" required :value="$role?->name"
                        placeholder="örn. editor" wrapper="mb-0" />

                    <x-admin::form.input name="label" help="role.label" label="Görünen Ad" required :value="$role?->label"
                        placeholder="örn. Editör" wrapper="mb-0" />

                    <x-admin::form.select name="guard_name" help="role.guard_name" label="Guard" required :options="$guards"
                        :value="$role?->guard_name ?? 'web'" :placeholder="null" wrapper="mb-0" />
                </div>
            </div>
        </div>

        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-center sm:justify-between gap-[15px]">
                <div class="trezo-card-title min-w-0">
                    <h5 class="!mb-[4px]">Yetkiler <x-admin::form.help topic="role.permissions" /></h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400 !mb-0">
                        <span data-permission-selected>0</span> yetki seçili
                    </p>
                    <x-admin::form.error name="permissions" />
                </div>
                <div class="trezo-card-subtitle mt-[15px] sm:mt-0 w-full sm:w-auto sm:max-w-[360px] grow">
                    <div class="relative">
                        <input type="text" id="role-permission-search" data-permission-search
                            placeholder="Modül veya yetki ara..." autocomplete="off"
                            class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                        <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                    </div>
                </div>
            </div>
        </div>

        <div data-permission-board>
            @foreach ($sections as $section)
                <section data-permission-section data-title="{{ $section['title'] }}" class="mb-[25px]">
                    <div class="flex items-center justify-between gap-[12px] mb-[15px] pb-[12px] border-b border-gray-100 dark:border-[#172036]">
                        <h5 class="!mb-0">{{ $section['title'] }}</h5>
                        <x-admin::form.switch bare label="Tümünü seç" wrapper="shrink-0 mb-0" data-select-section />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-[25px]">
                        @foreach ($section['groups'] as $group)
                            <div data-permission-group data-title="{{ $group['title'] }}"
                                class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md h-full">
                                <div class="trezo-card-header mb-[20px] flex items-center justify-between gap-[12px]">
                                    <div class="trezo-card-title min-w-0">
                                        <h5 class="!mb-0">{{ $group['title'] }}</h5>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">
                                            {{ $group['permissions']->count() }} yetki
                                        </span>
                                    </div>
                                    <x-admin::form.switch bare label="Tümünü seç" wrapper="shrink-0 mb-0" data-select-all />
                                </div>
                                <div class="trezo-card-content">
                                    @foreach ($group['permissions'] as $permission)
                                        <div data-permission-row class="mb-[15px] last:mb-0"
                                            data-label="{{ $permission->label }}"
                                            data-name="{{ $permission->name }}">
                                            <x-admin::form.switch
                                                :name="'permissions.'.$permission->id"
                                                :label="$permission->label"
                                                :checked="in_array($permission->id, $selected, true)"
                                                wrapper="mb-0"
                                                data-permission />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <p data-permission-empty class="hidden text-sm text-gray-500 dark:text-gray-400 mb-[25px]">
                Eşleşen modül veya yetki bulunamadı.
            </p>
        </div>

        <div class="flex items-center justify-end gap-[12px] mb-[25px]">
            <a href="{{ route('admin.role.index') }}"
                class="inline-block py-[10px] px-[25px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                Vazgeç
            </a>
            <button type="submit"
                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                {{ $role ? 'Güncelle' : 'Kaydet' }}
            </button>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/role/form.js') }}"></script>
@endpush
