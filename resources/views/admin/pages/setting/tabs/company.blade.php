<form id="setting-form" action="{{ route('admin.setting.company.update') }}" method="POST">
    @csrf
    @method('PUT')

    <x-admin::form.image name="logo_media_id" label="Firma logosu" :media="$media['logo_media_id'] ?? null"
        hint="Üst menü ve Google arama önizlemesinde kullanılır." />

    <x-admin::form.input name="name" label="Firma adı" required :value="$values['name'] ?? null" placeholder="Örn. Umay Dijital" />

    <x-admin::form.input name="legal_name" label="Yasal unvan" :value="$values['legal_name'] ?? null"
        placeholder="Örn. Umay Dijital Yazılım A.Ş." />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px] mb-[20px] md:mb-[25px]">
        <x-admin::form.input name="phone" label="Telefon" :value="$values['phone'] ?? null" placeholder="Örn. 0212 000 00 00"
            wrapper="" />

        <x-admin::form.input name="fax" label="Fax" :value="$values['fax'] ?? null" placeholder="Örn. 0212 000 00 01"
            wrapper="" />
    </div>

    <x-admin::form.input name="email" label="E-posta" type="email" :value="$values['email'] ?? null"
        placeholder="Örn. info@ornek.com" />

    <x-admin::form.textarea name="address" label="Adres" :value="$values['address'] ?? null" placeholder="Açık adres" />

    <x-admin::form.textarea name="short_description" label="Kısa Açıklama" :value="$values['short_description'] ?? null"
        placeholder="Site altbilgisinde (footer) firma logosunun altında görünür, 1-2 cümle yeterli." />

    <x-admin::form.map :lat="$values['latitude'] ?? null" :lng="$values['longitude'] ?? null" wrapper="" />

    <div
        class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[20px] border-t border-gray-100 dark:border-[#172036]">
        <button type="submit"
            class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
            Kaydet
        </button>
    </div>
</form>
