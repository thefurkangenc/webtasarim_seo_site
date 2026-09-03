{{--
    AJAX modal iskeleti. İçerik core/modal.js tarafından sunucudan çekilip
    #ajax-modal-body içine basılır; bu dosya sayfa başına bir kez, layout'ta
    include edilir.

    Açılma mekanizması template'e aittir: .add-new-popup elemanına .active
    class'ı eklenir (geçiş tanımı resources/css/admin/style.css içinde).
--}}
<div class="add-new-popup z-[999] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]"
    id="ajax-modal">
    <div class="popup-dialog flex transition-all max-w-[550px] min-h-full items-center mx-auto">
        <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">

            <div
                class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] md:mb-[25px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                <div class="trezo-card-title">
                    <h5 class="!mb-0" id="ajax-modal-title"></h5>
                </div>
                <div class="trezo-card-subtitle">
                    <button type="button" data-modal-close
                        class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                        <i class="ri-close-fill"></i>
                    </button>
                </div>
            </div>

            <div class="trezo-card-content pb-[20px] md:pb-[25px]" id="ajax-modal-body"></div>

        </div>
    </div>
</div>
