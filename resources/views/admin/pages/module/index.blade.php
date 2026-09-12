@extends('admin.layout.app')
@section('admin.title', 'Modül Yönetimi')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Modül Yönetimi</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Modül Yönetimi
            </li>
        </ol>
    </div>

    <form id="module-form" action="{{ route('admin.module.update') }}">
        @foreach ($modules as $module)
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between gap-[12px] flex-wrap">
                    <div class="trezo-card-title flex items-center gap-[10px]">
                        <i class="material-symbols-outlined !text-[22px] text-primary-500">{{ $module['icon'] }}</i>
                        <div>
                            <h5 class="!mb-0">{{ $module['label'] }}</h5>
                            <p class="text-gray-500 dark:text-gray-400 text-xs !mb-0">{{ $module['description'] }}</p>
                        </div>
                    </div>
                    <x-admin::form.switch :name="'modules.'.$module['key'].'.is_active'" label="Aktif"
                        :checked="$module['is_active']" wrapper="" />
                </div>
                <div class="trezo-card-content">
                    <x-admin::form.input :name="'modules.'.$module['key'].'.name'" label="Görünen Ad (opsiyonel)"
                        :value="$module['name']" :placeholder="$module['label']" />

                    @foreach ($module['presets'] as $preset)
                        <div class="grid grid-cols-2 gap-[15px]">
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.width'"
                                :label="$preset['label'].' — Genişlik (px)'" :value="$preset['width']" />
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.height'"
                                :label="$preset['label'].' — Yükseklik (px)'" :value="$preset['height']" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($generalPresets !== [])
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Genel Boyutlar</h5>
                        <p class="text-gray-500 dark:text-gray-400 text-xs !mb-0">Belirli bir modüle bağlı olmayan görsel boyutları.</p>
                    </div>
                </div>
                <div class="trezo-card-content">
                    @foreach ($generalPresets as $preset)
                        <div class="grid grid-cols-2 gap-[15px]">
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.width'"
                                :label="$preset['label'].' — Genişlik (px)'" :value="$preset['width']" />
                            <x-admin::form.input type="number" :name="'presets.'.$preset['field'].'.height'"
                                :label="$preset['label'].' — Yükseklik (px)'" :value="$preset['height']" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center justify-end">
            <button type="submit"
                class="inline-block py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                Kaydet
            </button>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/module/form.js') }}"></script>
@endpush
