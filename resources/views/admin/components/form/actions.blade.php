@props(['submit' => 'Kaydet', 'cancel' => 'Vazgeç'])

<div
    class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
    <button type="button" data-modal-close
        class="inline-block py-[10px] px-[30px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
        {{ $cancel }}
    </button>
    <button type="submit"
        class="inline-block py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
        {{ $submit }}
    </button>
</div>
