@extends('admin.layout.app')
@section('admin.title', 'Schema.org')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between gap-[15px]">
        <h5 class="!mb-0">Schema.org Doğrulama</h5>
        <div class="flex items-center gap-[12px] mt-[12px] md:mt-0">
            <a href="{{ route('admin.setting.edit', 'schema') }}"
                class="inline-flex items-center gap-[6px] py-[8px] px-[16px] text-sm text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[18px]">tune</i>
                Ayarlar
            </a>
            <ol class="breadcrumb">
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                        <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                        Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item inline-block relative text-sm mx-[11px]">Schema.org</li>
            </ol>
        </div>
    </div>

    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-content">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-[20px] leading-relaxed">
                Bir sayfa seçin ya da ön yüzden bir adres yapıştırın; o sayfa için üretilen
                <strong>JSON-LD (@graph)</strong> çıktısını, kural denetimini ve harici doğrulayıcılara
                giden bağlantıları burada görürsünüz.
            </p>

            <div class="flex flex-col md:flex-row gap-[15px] md:items-end">
                <div class="flex-1">
                    <label class="mb-[8px] text-sm text-black dark:text-white font-medium block">Hazır sayfa</label>
                    <select id="schema-target" data-choices class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 cursor-pointer focus:border-primary-500">
                        <option value="">— seçin —</option>
                        @foreach ($samples as $group)
                            <optgroup label="{{ $group['label'] }}">
                                @foreach ($group['items'] as $item)
                                    <option value="{{ $item['url'] }}">{{ $item['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1">
                    <label class="mb-[8px] text-sm text-black dark:text-white font-medium block">…ya da adres</label>
                    <input type="text" id="schema-url" placeholder="/hizmetler/web-tasarim"
                        class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all focus:border-primary-500">
                </div>

                <button type="button" id="schema-run"
                    class="h-[42px] shrink-0 inline-flex items-center justify-center gap-[6px] py-[9px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500">
                    <i class="material-symbols-outlined !text-[19px]">frame_inspect</i>
                    Göster
                </button>
            </div>
        </div>
    </div>

    <div id="schema-result" hidden>
        <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-content">
                <div class="flex flex-wrap items-center gap-[10px] mb-[18px]">
                    <a id="schema-open" href="#" target="_blank" rel="noopener"
                        class="text-sm text-primary-500 hover:underline inline-flex items-center gap-[4px]">
                        <i class="material-symbols-outlined !text-[16px]">open_in_new</i>
                        <span data-target-url></span>
                    </a>
                    <span data-target-kind class="text-[10px] font-medium py-[2px] px-[8px] rounded-sm bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-300"></span>
                    <span data-target-unresolved hidden class="text-[10px] font-medium py-[2px] px-[8px] rounded-sm bg-warning-100 text-warning-600">
                        adres bir sayfaya oturmadı — genel çıktı
                    </span>
                </div>

                <div class="flex flex-wrap gap-[10px] mb-[20px]">
                    <a id="schema-google" href="#" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-sm rounded-md border border-gray-200 dark:border-[#172036] text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px]">travel_explore</i>
                        Google Rich Results Test
                    </a>
                    <a id="schema-validator" href="#" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-sm rounded-md border border-gray-200 dark:border-[#172036] text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px]">verified</i>
                        Schema.org Validator
                    </a>
                </div>

                <div id="schema-lint" class="grid grid-cols-1 md:grid-cols-3 gap-[12px] mb-[20px]"></div>
                <div id="schema-lint-detail" class="space-y-[8px] mb-[20px]"></div>

                <div class="flex items-center justify-between mb-[10px]">
                    <span class="text-sm font-medium text-black dark:text-white">Üretilen JSON-LD</span>
                    <button type="button" id="schema-copy"
                        class="inline-flex items-center gap-[5px] py-[6px] px-[12px] text-xs rounded-md border border-gray-200 dark:border-[#172036] text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[15px]">content_copy</i> Kopyala
                    </button>
                </div>
                <pre id="schema-json" class="text-xs leading-relaxed p-[16px] rounded-md bg-gray-50 dark:bg-[#15203c] border border-gray-100 dark:border-[#172036] overflow-x-auto text-black dark:text-gray-200"></pre>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/schema/index.js') }}"></script>
@endpush
