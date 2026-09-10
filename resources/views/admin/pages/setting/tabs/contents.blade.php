<form id="setting-form" action="{{ route('admin.setting.contents.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="settings-panel mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">info</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Hakkımızda</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Kurumsal hakkımızda sayfasının başlığı ve metni.</p>
            </div>
        </div>

        <x-admin::form.input name="about_title" help="contents.about_title" label="Başlık"
            :value="$values['about_title'] ?? null"
            placeholder="Örn. Hakkımızda" />

        <x-admin::form.editor name="about_content" help="contents.about_content" label="İçerik"
            :value="$values['about_content'] ?? null"
            :height="420"
            wrapper="mb-0" />
    </div>

    <div class="settings-panel mb-[20px] md:mb-[25px]">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">cookie</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">Çerez metni</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Çerez politikası sayfasında gösterilecek metin.</p>
            </div>
        </div>

        <x-admin::form.editor name="cookie_content" help="contents.cookie_content"
            :value="$values['cookie_content'] ?? null"
            :height="380"
            wrapper="mb-0" />
    </div>

    <div class="settings-panel">
        <div class="flex items-center gap-[12px] mb-[20px]">
            <span class="settings-chip w-[40px] h-[40px] flex items-center justify-center shrink-0">
                <i class="material-symbols-outlined !text-[22px] text-primary-500">policy</i>
            </span>
            <div class="min-w-0">
                <p class="!mb-[3px] font-medium text-black dark:text-white">KVKK</p>
                <p class="!mb-0 text-xs text-gray-500 dark:text-gray-400">Kişisel verilerin korunması aydınlatma metni.</p>
            </div>
        </div>

        <x-admin::form.editor name="kvkk_content" help="contents.kvkk_content"
            :value="$values['kvkk_content'] ?? null"
            :height="380"
            wrapper="mb-0" />
    </div>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
