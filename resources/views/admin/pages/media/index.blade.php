@extends('admin.layout.app')
@section('admin.title', 'Medya Kütüphanesi')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Medya Kütüphanesi</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Medya
            </li>
        </ol>
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-content">
            @include('admin.pages.media.partials.browser', [
                'selectable' => false,
                'manageable' => true,
            ])
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/media/index.js') }}"></script>
@endpush
