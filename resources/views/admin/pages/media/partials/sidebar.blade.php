{{--
    Dosya yöneticisinin sol paneli — sadece /admin/media sayfasında, picker
    modalında yok. İşlevi pages/media/index.js verir: hızlı erişim kısayolları,
    açılıp kapanan klasör ağacı ve depolama özeti — hepsi gerçek veriden,
    sayfa yüklenince bir kerede çekilir.

    Aktif öğe işaretlemesi de o dosyada: MediaBrowser'ın onNavigate geri
    çağrısını dinler, böylece breadcrumb veya çift tıklamayla gezinildiğinde de
    doğru satır vurgulanır.
--}}
<div data-media-sidebar class="trezo-card bg-white dark:bg-[#0c1427] p-[15px] md:p-[18px] rounded-md">
    <div class="trezo-card-content">

        {{-- Hızlı erişim --}}
        <ul class="flex flex-col gap-[3px]">
            <li>
                <button type="button" data-sidebar-action="root"
                    class="flex items-center gap-[10px] w-full text-left rounded-[10px] py-[9px] px-[12px] text-sm font-medium transition-all text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="ri-hard-drive-2-fill text-[18px] text-primary-500 shrink-0 leading-none"></i>
                    Tüm Medya
                </button>
            </li>
            <li>
                <button type="button" data-sidebar-action="recent"
                    class="flex items-center gap-[10px] w-full text-left rounded-[10px] py-[9px] px-[12px] text-sm font-medium transition-all text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="ri-time-fill text-[18px] text-purple-500 shrink-0 leading-none"></i>
                    Son Eklenenler
                </button>
            </li>
            <li>
                <button type="button" data-sidebar-action="unattached"
                    class="flex items-center gap-[10px] w-full text-left rounded-[10px] py-[9px] px-[12px] text-sm font-medium transition-all text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    <i class="ri-link-unlink text-[18px] text-warning-600 shrink-0 leading-none"></i>
                    Bağlantısız
                </button>
            </li>
        </ul>

        {{-- Klasör ağacı — JS dolduruyor --}}
        <div class="mt-[16px] pt-[16px] border-t border-gray-100 dark:border-[#172036]">
            <h6 class="!mb-[8px] !text-[11px] font-semibold uppercase tracking-[.06em] text-gray-500 dark:text-gray-400">
                Klasörler
            </h6>
            <ul data-sidebar-folders class="flex flex-col gap-[2px]">
                <li class="text-sm text-gray-500 dark:text-gray-400 px-[12px] py-[6px]">Yükleniyor...</li>
            </ul>
        </div>

        {{-- Depolama --}}
        <div class="mt-[16px] pt-[16px] border-t border-gray-100 dark:border-[#172036]">
            <h6 class="!mb-[10px] !text-[11px] font-semibold uppercase tracking-[.06em] text-gray-500 dark:text-gray-400">
                Depolama
            </h6>
            <div class="h-[6px] w-full rounded-full bg-gray-100 dark:bg-[#172036] overflow-hidden">
                <span data-sidebar-stats-bar class="block h-full rounded-full bg-primary-500 transition-all duration-500"
                    style="width: 0%"></span>
            </div>
            <span data-sidebar-stats-text class="block text-xs text-gray-500 dark:text-gray-400 mt-[8px]">
                Yükleniyor...
            </span>
        </div>

    </div>
</div>
