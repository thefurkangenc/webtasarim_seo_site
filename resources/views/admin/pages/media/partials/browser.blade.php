{{--
    Medya tarayıcısı. Hem /admin/media sayfası hem de form içinden açılan
    seçici modal bu markup'ı kullanır; davranışı core/media-browser.js verir.

    $folders    : kök klasörler (children ile)
    $selectable : true ise kart tıklaması seçim yapar
    $manageable : true ise silme/düzenleme/klasör işlemleri görünür
--}}
@php
    $selectable = $selectable ?? false;
    $manageable = $manageable ?? false;
@endphp

<div data-media-browser data-selectable="{{ $selectable ? '1' : '' }}"
    data-manageable="{{ $manageable ? '1' : '' }}">

    <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-[20px]">

        {{-- Klasör ağacı --}}
        <aside class="border border-gray-100 dark:border-[#172036] rounded-md p-[10px] h-fit">
            <div class="flex items-center justify-between mb-[8px] px-[10px]">
                <span class="text-xs font-medium uppercase text-gray-400">Klasörler</span>
                @if ($manageable)
                    <button type="button" data-media-action="folder-create" title="Yeni klasör"
                        class="text-gray-500 dark:text-gray-400 transition-all hover:text-primary-500">
                        <i class="material-symbols-outlined !text-[18px] leading-none">create_new_folder</i>
                    </button>
                @endif
            </div>

            <ul data-media-folders>
                <li>
                    <button type="button" data-folder-id=""
                        class="media-folder active w-full text-left rounded-md flex items-center gap-[7px] py-[7px] px-[10px] transition-all font-medium text-gray-500 dark:text-gray-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px] leading-none">inbox</i>
                        <span>Tümü</span>
                    </button>
                </li>
                @foreach ($folders as $folder)
                    @include('admin.pages.media.partials.folder-node', ['folder' => $folder, 'depth' => 1])
                @endforeach
            </ul>

            @if ($manageable)
                <div class="border-t border-gray-100 dark:border-[#172036] mt-[10px] pt-[10px]">
                    <button type="button" data-media-filter="unattached"
                        class="media-folder w-full text-left rounded-md flex items-center gap-[7px] py-[7px] px-[10px] transition-all font-medium text-gray-500 dark:text-gray-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-[#15203c]">
                        <i class="material-symbols-outlined !text-[18px] leading-none">link_off</i>
                        <span>Bağlantısız</span>
                    </button>
                </div>
            @endif
        </aside>

        <div>
            {{-- Araç çubuğu --}}
            <div class="flex items-center gap-[10px] flex-wrap mb-[15px]">
                <div class="relative grow max-w-[280px]">
                    <input type="text" data-media-search placeholder="Dosya ara..."
                        class="bg-gray-50 border border-gray-50 h-[40px] rounded-md w-full block text-black ltr:pl-[13px] rtl:pr-[13px] ltr:pr-[38px] rtl:pl-[38px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <i
                        class="material-symbols-outlined !text-[19px] absolute text-gray-500 ltr:right-[12px] rtl:left-[12px] top-1/2 -translate-y-1/2">search</i>
                </div>

                <select data-media-type
                    class="h-[40px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[13px] outline-0 cursor-pointer transition-all focus:border-primary-500">
                    <option value="">Tüm türler</option>
                    <option value="image">Görseller</option>
                    <option value="other">Diğer</option>
                </select>

                <button type="button" data-media-action="upload"
                    class="ltr:ml-auto rtl:mr-auto inline-flex items-center gap-[6px] py-[9px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                    <i class="material-symbols-outlined !text-[19px]">upload</i>
                    Yükle
                </button>
                <input type="file" data-media-upload-input multiple accept="image/*,.svg" class="hidden">
            </div>

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
    </div>
</div>
