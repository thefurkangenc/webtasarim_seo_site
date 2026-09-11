<form id="setting-form" action="{{ route('admin.setting.seo.update') }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Site geneli varsayılanlar bir içerik kaydına bağlı değil: odak kelime
         ve canlı skor paneli burada anlamsız, kapatılır. --}}
    <x-admin::form.seo :values="$values" :og-media="$media['og_media_id'] ?? null" prefix=""
        :analysis="false" wrapper="">
        <x-slot:before>
            <x-admin::form.input name="site_name" label="Site adı" help="seo.site_name"
                :value="$values['site_name'] ?? null"
                data-seo-input="site_name"
                placeholder="Boş bırakılırsa firma adı kullanılır" />
        </x-slot:before>
    </x-admin::form.seo>

    <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
