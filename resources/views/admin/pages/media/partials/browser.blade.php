{{--
    Medya tarayıcısı — Google Drive benzeri dosya yöneticisi görünümü. Hem
    /admin/media sayfası hem de form içinden açılan seçici modal bu markup'ı
    kullanır; davranışı core/media-browser.js verir.

    Yerleşim: klasörler ve dosyalar Drive'daki gibi başlıklı iki ayrı bölümde
    durur, ikisi de [data-media-grid] sarmalayıcısının içindedir — o sarmalayıcı
    ızgara/liste anahtarının ve shift ile aralık seçiminin dayanağıdır, bu
    yüzden bölümler ondan DIŞARI çıkarılmamalı.

    $selectable  : true ise çift tık / "Seç" bir dosyayı seçip modalı kapatır
    $manageable  : true ise context menu, sürükle-taşı, çoklu seçim aktif
--}}
@php
    $selectable = $selectable ?? false;
    $manageable = $manageable ?? false;
@endphp

<div data-media-browser data-selectable="{{ $selectable ? '1' : '' }}"
    data-manageable="{{ $manageable ? '1' : '' }}">

    {{-- Araç çubuğu --}}
    <div class="flex items-center gap-[10px] flex-wrap mb-[18px]">
        <nav data-media-breadcrumb class="flex items-center gap-[2px] flex-wrap min-w-0"></nav>

        <div class="relative grow max-w-[260px] ltr:ml-auto rtl:mr-auto">
            <input type="text" data-media-search placeholder="Dosya ara..."
                class="bg-gray-50 border border-gray-50 h-[40px] rounded-[10px] w-full block text-black ltr:pl-[38px] rtl:pr-[38px] ltr:pr-[13px] rtl:pl-[13px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
            <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:left-[12px] rtl:right-[12px] top-1/2 -translate-y-1/2">search</i>
        </div>

        <select data-media-type data-choices
            class="h-[40px] rounded-[10px] text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
            <option value="">Tüm türler</option>
            <option value="image">Görseller</option>
            <option value="other">Diğer</option>
        </select>

        {{-- Izgara / liste görünüm anahtarı --}}
        <div data-media-view-toggle
            class="inline-flex items-center gap-[2px] p-[3px] rounded-[10px] bg-gray-50 dark:bg-[#15203c]">
            <button type="button" data-media-view="list" title="Liste görünümü"
                class="w-[32px] h-[32px] inline-flex items-center justify-center rounded-[7px] text-gray-500 dark:text-gray-400 transition-all">
                <i class="material-symbols-outlined !text-[19px]">view_list</i>
            </button>
            <button type="button" data-media-view="grid" title="Izgara görünümü"
                class="w-[32px] h-[32px] inline-flex items-center justify-center rounded-[7px] bg-white dark:bg-[#0c1427] text-primary-500 shadow-sm transition-all">
                <i class="material-symbols-outlined !text-[19px]">grid_view</i>
            </button>
        </div>

        @if ($manageable)
            <button type="button" data-media-action="folder-create"
                class="inline-flex items-center gap-[6px] h-[40px] px-[16px] text-black dark:text-white transition-all rounded-[10px] border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[19px]">create_new_folder</i>
                Yeni Klasör
            </button>
        @endif

        <button type="button" data-media-action="upload"
            class="inline-flex items-center gap-[6px] h-[40px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-[10px] border border-primary-500 hover:border-primary-400">
            <i class="material-symbols-outlined !text-[19px]">upload</i>
            Yükle
        </button>
        <input type="file" data-media-upload-input multiple accept="image/*,.svg" class="hidden">
    </div>

    {{-- Çoklu seçim aksiyon çubuğu — seçim varken görünür, yoksa gizli --}}
    @if ($manageable)
        <div data-media-bulkbar
            class="hidden items-center gap-[10px] flex-wrap mb-[15px] bg-primary-50 dark:bg-[#15203c] border border-primary-100 dark:border-[#172036] rounded-[12px] py-[10px] px-[15px]">
            <span data-media-bulk-count class="text-sm font-medium text-primary-500"></span>
            <button type="button" data-media-action="bulk-move"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-[8px] border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-gray-50">
                <i class="material-symbols-outlined !text-[16px]">drive_file_move</i> Taşı
            </button>
            <button type="button" data-media-action="bulk-delete"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-danger-500 transition-all rounded-[8px] border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-danger-100">
                <i class="material-symbols-outlined !text-[16px]">delete</i> Sil
            </button>
            <button type="button" data-media-action="bulk-clear"
                class="ltr:ml-auto rtl:mr-auto text-xs text-gray-500 dark:text-gray-400 hover:text-primary-500 transition-all">
                Seçimi Temizle
            </button>
        </div>
    @endif

    {{-- Sürükle-bırak alanı + içerik --}}
    <div data-media-dropzone class="relative min-h-[300px] transition-all">

        <div data-media-drop-hint
            class="hidden flex absolute inset-0 z-[2] bg-primary-50/95 dark:bg-[#15203c]/95 rounded-[14px] border-2 border-dashed border-primary-300 dark:border-primary-500/50 items-center justify-center pointer-events-none">
            <span class="text-primary-500 font-medium flex flex-col items-center gap-[6px]">
                <i class="material-symbols-outlined !text-[38px]">cloud_upload</i>
                Yüklemek için bırakın
            </span>
        </div>

        {{-- Izgara görünümü: iki bölüm tek sarmalayıcının içinde --}}
        <div data-media-grid>
            <section data-media-folders-section class="hidden mb-[22px]">
                <h6 class="!mb-[10px] !text-[11px] font-semibold uppercase tracking-[.06em] text-gray-500 dark:text-gray-400">
                    Klasörler
                </h6>
                <div data-media-folders class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[10px]"></div>
            </section>

            <section data-media-files-section class="hidden">
                <h6 class="!mb-[10px] !text-[11px] font-semibold uppercase tracking-[.06em] text-gray-500 dark:text-gray-400">
                    Dosyalar
                </h6>
                <div data-media-files
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-[14px]"></div>
            </section>
        </div>

        <div data-media-list class="hidden table-responsive overflow-x-auto">
            <table class="w-full">
                <thead class="text-black dark:text-white">
                    <tr>
                        <th class="font-medium ltr:text-left rtl:text-right px-[15px] py-[10px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md">Ad</th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[15px] py-[10px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap">Değiştirilme Tarihi</th>
                        <th class="font-medium ltr:text-left rtl:text-right px-[15px] py-[10px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap last:rounded-tr-md">Boyut</th>
                    </tr>
                </thead>
                <tbody data-media-list-body class="text-black dark:text-white"></tbody>
            </table>
        </div>

        <div data-media-status class="py-[60px] text-center text-gray-500 dark:text-gray-400"></div>
    </div>

    <div data-media-pagination class="mt-[18px]"></div>
</div>
