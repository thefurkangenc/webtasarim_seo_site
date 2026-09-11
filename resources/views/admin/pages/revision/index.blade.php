@extends('admin.layout.app')
@section('admin.title', 'Revizyonlar')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Revizyonlar</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Revizyonlar</li>
        </ol>
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-start sm:justify-between">
            <div class="trezo-card-title">
                <h5 class="!mb-0">İçerik Sürümleri</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-[4px] max-w-[620px]">
                    Bir kayıt her düzenlendiğinde önceki hali buraya düşer. Karşılaştır ikonu sürümü
                    kaydın şu anki haliyle yan yana gösterir, oradan tek tuşla o sürüme dönebilirsiniz.
                    Geri yükleme içeriğin yanı sıra SEO alanlarını, etiketleri ve görsel bağlarını da kapsar.
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-[6px]">
                    Toplam <strong class="text-black dark:text-white">{{ number_format($total) }}</strong> sürüm
                    saklanıyor · kayıt başına en fazla {{ $keep }} sürüm tutulur, eskiler kendiliğinden silinir.
                </p>
            </div>
        </div>

        <div class="trezo-card-content">
            <div class="flex items-center gap-[10px] flex-wrap mb-[20px]">
                <div class="relative grow max-w-[280px]">
                    <input type="text" id="revision-search" placeholder="Kayıt adına göre ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>

                <select id="revision-module" data-choices
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm içerik türleri</option>
                    @foreach ($modules as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="table-responsive overflow-x-auto">
                <table class="w-full">
                    <thead class="text-black dark:text-white">
                        <tr>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md">Tarih</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Kayıt</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Bu sürümde değişenler</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Kullanıcı</th>
                            <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="text-black dark:text-white" id="revision-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/revision/index.js') }}"></script>
@endpush
