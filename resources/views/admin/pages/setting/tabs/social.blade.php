<div class="flex items-center justify-end mb-[20px] md:mb-[25px]">
    @can('setting.update')
        <button type="button" id="social-link-create"
            class="inline-flex items-center gap-[6px] py-[9px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            <i class="material-symbols-outlined !text-[19px]">add</i>
            Yeni Ekle
        </button>
    @endcan
</div>

<p id="social-link-empty" class="text-sm text-gray-500 dark:text-gray-400">
    Henüz sosyal medya eklenmedi.
</p>

<div id="social-link-grid" data-can-update="{{ auth()->user()->can('setting.update') ? '1' : '0' }}"
    class="hidden grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]"></div>
