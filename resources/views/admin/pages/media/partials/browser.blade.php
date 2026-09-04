{{--
    Medya tarayıcısı — dosya yöneticisi görünümü. Hem /admin/media sayfası hem
    de form içinden açılan seçici modal bu markup'ı kullanır; davranışı
    core/media-browser.js verir. Klasörler de dosyalarla aynı ızgarada kart
    olarak durur; gezinme sidebar ağacı değil breadcrumb + çift tıklamayladır.

    $rootFolders : kök klasörler (ilk boyama için; sonraki gezinme AJAX'la olur)
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
    <div class="flex items-center gap-[10px] flex-wrap mb-[15px]">
        <nav data-media-breadcrumb class="flex items-center gap-[4px] flex-wrap text-sm font-medium"></nav>

        <div class="relative grow max-w-[240px] ltr:ml-auto rtl:mr-auto">
            <input type="text" data-media-search placeholder="Dosya ara..."
                class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
            <i class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
        </div>

        <select data-media-type
            class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
            <option value="">Tüm türler</option>
            <option value="image">Görseller</option>
            <option value="other">Diğer</option>
        </select>

        @if ($manageable)
            <button type="button" data-media-action="folder-create"
                class="inline-flex items-center gap-[6px] py-[9px] px-[16px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <i class="material-symbols-outlined !text-[19px]">create_new_folder</i>
                Yeni Klasör
            </button>
        @endif

        <button type="button" data-media-action="upload"
            class="inline-flex items-center gap-[6px] py-[9px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            <i class="material-symbols-outlined !text-[19px]">upload</i>
            Yükle
        </button>
        <input type="file" data-media-upload-input multiple accept="image/*,.svg" class="hidden">
    </div>

    {{-- Çoklu seçim aksiyon çubuğu — seçim varken görünür, yoksa gizli --}}
    @if ($manageable)
        <div data-media-bulkbar
            class="hidden items-center gap-[10px] flex-wrap mb-[15px] bg-primary-50 dark:bg-[#15203c] rounded-md py-[10px] px-[15px]">
            <span data-media-bulk-count class="text-sm font-medium text-primary-500"></span>
            <button type="button" data-media-action="bulk-move"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-gray-50">
                <i class="material-symbols-outlined !text-[16px]">drive_file_move</i> Taşı
            </button>
            <button type="button" data-media-action="bulk-delete"
                class="inline-flex items-center gap-[5px] py-[7px] px-[14px] text-xs text-danger-500 transition-all rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] hover:bg-danger-100">
                <i class="material-symbols-outlined !text-[16px]">delete</i> Sil
            </button>
            <button type="button" data-media-action="bulk-clear"
                class="ltr:ml-auto rtl:mr-auto text-xs text-gray-500 dark:text-gray-400 hover:text-primary-500 transition-all">
                Seçimi Temizle
            </button>
        </div>
    @endif

    {{-- Sürükle-bırak alanı + ızgara --}}
    <div data-media-dropzone
        class="relative border-2 border-dashed border-gray-100 dark:border-[#172036] rounded-md p-[15px] min-h-[280px] transition-all">

        <div data-media-drop-hint
            class="hidden absolute inset-0 z-[2] bg-primary-50/90 dark:bg-[#15203c]/90 rounded-md flex items-center justify-center pointer-events-none">
            <span class="text-primary-500 font-medium flex items-center gap-[8px]">
                <i class="material-symbols-outlined">cloud_upload</i> Yüklemek için bırakın
            </span>
        </div>

        <div data-media-grid class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-[12px]"></div>
        <div data-media-status class="py-[60px] text-center text-gray-500 dark:text-gray-400"></div>
    </div>

    <div data-media-pagination class="mt-[15px]"></div>
</div>
