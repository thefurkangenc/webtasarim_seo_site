{{--
    Dosya yöneticisinin sol paneli — sadece /admin/media sayfasında, picker
    modalında yok. İşlevi core/pages/media/index.js verir: kök klasör
    kısayolları, "Son Eklenenler"/"Bağlantısız" ve depolama özeti — hepsi
    gerçek veriden, sayfa yüklenince bir kerede çekilir.
--}}
<div data-media-sidebar class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
    <div class="trezo-card-content">
        <ul class="mb-[20px] md:mb-[25px]">
            <li class="font-medium mb-[15px] md:mb-[19px] last:mb-0">
                <button type="button" data-sidebar-action="root"
                    class="w-full text-left relative flex items-center ltr:pl-[28px] rtl:pr-[28px] transition-all text-primary-500">
                    <i class="material-symbols-outlined absolute !text-lg ltr:left-0 rtl:right-0 top-1/2 -translate-y-1/2 -mt-[.5px]">perm_media</i>
                    Tüm Medya
                </button>
                <ul data-sidebar-folders class="ltr:pl-[28px] rtl:pr-[28px] mt-[15px] md:mt-[17px] mb-[17px] md:mb-[21px]"></ul>
            </li>
            <li class="font-medium mb-[15px] md:mb-[19px] last:mb-0">
                <button type="button" data-sidebar-action="recent"
                    class="w-full text-left relative flex items-center ltr:pl-[28px] rtl:pr-[28px] transition-all text-black dark:text-white hover:text-primary-500">
                    <i class="material-symbols-outlined text-purple-500 absolute !text-lg ltr:left-0 rtl:right-0 top-1/2 -translate-y-1/2 -mt-[.5px]">schedule</i>
                    Son Eklenenler
                </button>
            </li>
            <li class="font-medium mb-[15px] md:mb-[19px] last:mb-0">
                <button type="button" data-sidebar-action="unattached"
                    class="w-full text-left relative flex items-center ltr:pl-[28px] rtl:pr-[28px] transition-all text-black dark:text-white hover:text-primary-500">
                    <i class="material-symbols-outlined text-warning-500 absolute !text-lg ltr:left-0 rtl:right-0 top-1/2 -translate-y-1/2 -mt-[.5px]">link_off</i>
                    Bağlantısız
                </button>
            </li>
        </ul>
        <div class="border-t border-gray-100 dark:border-[#172036] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px]">
            <h6 class="!mb-[11px] !text-[15px]">Depolama</h6>
            <span data-sidebar-stats-text class="block text-sm text-gray-500 dark:text-gray-400">Yükleniyor...</span>
        </div>
    </div>
</div>
