{{-- CSV içe aktarma sonucu. pages/redirect/index.js açar/doldurur. --}}
<div class="add-new-popup z-[1400] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]"
    data-import-result-modal>
    <div class="popup-dialog flex transition-all max-w-[520px] min-h-full items-center mx-auto">
        <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] md:mb-[25px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                <div class="trezo-card-title">
                    <h5 class="!mb-0">İçe Aktarma Sonucu</h5>
                </div>
                <button type="button" data-modal-close
                    class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                    <i class="ri-close-fill"></i>
                </button>
            </div>

            <div class="trezo-card-content">
                <div class="grid grid-cols-3 gap-[10px] mb-[16px] text-center">
                    <div class="p-[12px] rounded-md bg-success-100 dark:bg-[#15203c]">
                        <span class="block text-lg font-bold text-success-600" data-import-created>0</span>
                        <span class="block text-[11px] text-gray-500 dark:text-gray-400">Eklendi</span>
                    </div>
                    <div class="p-[12px] rounded-md bg-primary-100 dark:bg-[#15203c]">
                        <span class="block text-lg font-bold text-primary-500" data-import-updated>0</span>
                        <span class="block text-[11px] text-gray-500 dark:text-gray-400">Güncellendi</span>
                    </div>
                    <div class="p-[12px] rounded-md bg-warning-100 dark:bg-[#15203c]">
                        <span class="block text-lg font-bold text-warning-600" data-import-skipped>0</span>
                        <span class="block text-[11px] text-gray-500 dark:text-gray-400">Atlandı</span>
                    </div>
                </div>

                <div data-import-errors class="hidden">
                    <p class="text-xs font-medium text-danger-500 mb-[6px]">Atlanan satırlar:</p>
                    <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-[4px] max-h-[200px] overflow-y-auto list-disc ltr:pl-[18px] rtl:pr-[18px]"></ul>
                </div>

                <div class="flex justify-end mt-[20px]">
                    <button type="button" data-modal-close
                        class="inline-block py-[9px] px-[22px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md">
                        Tamam
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
