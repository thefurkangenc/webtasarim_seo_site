{{--
    Yapay zeka üretim modalının gövdesi. core/ai-generator.js tarafından
    çekilir; form ve ilerleme panelleri aynı gövdede durur, JS aralarında geçer.

    $promptKey : modül anahtarı (blog.content)
    $prompts   : o anahtara ait aktif şablonlar
--}}
<div data-ai-generator data-ai-key="{{ $promptKey }}">

    @if ($prompts->isEmpty())
        <div class="text-center py-[30px]">
            <i class="material-symbols-outlined !text-[42px] text-gray-400">robot_2</i>
            <p class="!mb-[5px] mt-[10px] font-medium text-black dark:text-white">
                Bu modül için tanımlı şablon yok
            </p>
            <p class="!mb-[15px] text-sm text-gray-500 dark:text-gray-400">
                Yapay Zeka → Prompt Şablonları ekranından <code class="text-xs">{{ $promptKey }}</code>
                anahtarıyla bir şablon ekleyin.
            </p>
            <a href="{{ route('admin.ai-prompt.index') }}"
                class="inline-flex items-center gap-[6px] py-[9px] px-[20px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                <i class="material-symbols-outlined !text-[19px]">open_in_new</i> Şablonlara Git
            </a>
        </div>
    @else
        {{-- 1. Form --}}
        <form id="ai-generate-form" data-ai-panel="form">
            <x-admin::form.select name="ai_prompt_id" label="Şablon" required
                :options="$prompts->pluck('name', 'id')->all()"
                :value="$prompts->firstWhere('is_default', true)?->id ?? $prompts->first()->id"
                :placeholder="null" />

            <x-admin::form.textarea name="input.keywords" label="Anahtar Kelimeler" required rows="2"
                placeholder="virgülle ayırın: kurumsal web tasarım, seo uyumlu site"
                class="h-[80px]" />

            <x-admin::form.input name="input.title" label="Başlık"
                placeholder="Boş bırakırsanız başlığı da yapay zeka üretir" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
                <x-admin::form.select name="input.length" label="Uzunluk"
                    :options="['kısa (400-600 kelime)' => 'Kısa', 'orta (800-1200 kelime)' => 'Orta', 'uzun (1500-2000 kelime)' => 'Uzun']"
                    value="orta (800-1200 kelime)" :placeholder="null"
                    wrapper="mb-[20px] md:mb-[25px]" />

                <x-admin::form.input name="input.category" label="Kategori"
                    placeholder="Yazının bağlamı" wrapper="mb-[20px] md:mb-[25px]" />
            </div>

            <x-admin::form.textarea name="input.notes" label="Ek Notlar" rows="2"
                placeholder="Üslup, hedef kitle, mutlaka geçmesi gereken bilgiler..." class="h-[80px]" />

            <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
                <button type="button" data-ai-close
                    class="inline-block py-[10px] px-[30px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    Vazgeç
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-[6px] py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                    <i class="material-symbols-outlined !text-[19px]">auto_awesome</i> Üret
                </button>
            </div>
        </form>

        {{-- 2. İlerleme --}}
        <div data-ai-panel="progress" class="hidden text-center py-[40px]">
            <div class="relative w-[70px] h-[70px] mx-auto mb-[18px]">
                <span class="absolute inset-0 rounded-full border-[3px] border-primary-100 dark:border-[#172036]"></span>
                <span class="absolute inset-0 rounded-full border-[3px] border-transparent border-t-primary-500 animate-spin"></span>
                <i class="material-symbols-outlined !text-[26px] text-primary-500 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">auto_awesome</i>
            </div>

            <p class="!mb-[5px] font-medium text-black dark:text-white" data-ai-status>Üretim kuyruğa alındı...</p>
            <p class="!mb-0 text-sm text-gray-500 dark:text-gray-400">
                Geçen süre: <span data-ai-elapsed>0</span> sn
            </p>

            {{-- Kuyruk işçisi çalışmıyorsa kayıt "queued" durumunda asılı kalır;
                 sessizce beklemek yerine bunu söylüyoruz. --}}
            <div data-ai-worker-warning
                class="hidden mt-[18px] mx-auto max-w-[420px] rounded-md border border-warning-300 bg-warning-100 dark:bg-[#15203c] dark:border-[#172036] p-[12px] text-left">
                <p class="!mb-[3px] font-medium text-warning-600 dark:text-warning-500 text-sm">İş hâlâ sırada bekliyor</p>
                <p class="!mb-0 text-xs text-gray-600 dark:text-gray-400">
                    Kuyruk işçisi çalışmıyor olabilir. Terminalde
                    <code class="text-xs">php artisan queue:work</code> komutunun açık olduğundan emin olun.
                </p>
            </div>

            <button type="button" data-ai-close
                class="mt-[20px] inline-block py-[9px] px-[24px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                Arka planda bırak
            </button>
        </div>

        {{-- 3. Hata --}}
        <div data-ai-panel="error" class="hidden py-[20px]">
            <div class="rounded-md border border-danger-300 bg-danger-100 dark:bg-[#15203c] dark:border-[#172036] p-[15px]">
                <p class="!mb-[5px] font-medium text-danger-600 dark:text-danger-500">Üretim tamamlanamadı</p>
                <p class="!mb-0 text-sm text-gray-700 dark:text-gray-400 break-words" data-ai-error></p>
            </div>

            <div class="flex items-center justify-end gap-[12px] mt-[20px]">
                <button type="button" data-ai-close
                    class="inline-block py-[10px] px-[30px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                    Kapat
                </button>
                <button type="button" data-ai-retry
                    class="inline-flex items-center gap-[6px] py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                    <i class="material-symbols-outlined !text-[19px]">refresh</i> Tekrar dene
                </button>
            </div>
        </div>
    @endif
</div>
