@extends('admin.layout.app')
@section('admin.title', 'Site Ayarları')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">Site Ayarları</h5>
        <div class="flex items-center gap-[12px] mt-[12px] md:mt-0">
            <x-admin::activity-log-button module="setting" class="!py-[7px] !px-[14px] text-sm" />
            <ol class="breadcrumb">
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                        <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                        Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                    Site Ayarları
                </li>
            </ol>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-[25px]">
        <div class="lg:col-span-1">
            @include('admin.pages.setting.partials.sidebar')
        </div>

        <div class="lg:col-span-3">
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">{{ $groups[$group]['title'] }}</h5>
                    </div>
                </div>
                <div class="trezo-card-content">
                    @include('admin.pages.setting.tabs.'.$group)
                </div>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    @if ($group === 'social')
        <script type="module" src="{{ asset('admin/assets/js/pages/setting/social.js') }}"></script>
    @elseif ($group === 'integrations')
        <script type="module" src="{{ asset('admin/assets/js/pages/setting/integration.js') }}"></script>
    @else
        <script type="module" src="{{ asset('admin/assets/js/pages/setting/form.js') }}"></script>
        @if ($group === 'mail')
            <script type="module" src="{{ asset('admin/assets/js/pages/setting/mail.js') }}"></script>
        @endif
        @if ($group === 'contact')
            <script type="module" src="{{ asset('admin/assets/js/pages/setting/contact.js') }}"></script>
        @endif
        @if ($group === 'tracking')
            <script type="module" src="{{ asset('admin/assets/js/pages/setting/tracking.js') }}"></script>
        @endif
    @endif
@endpush
