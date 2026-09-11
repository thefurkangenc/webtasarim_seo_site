{{-- AJAX modal gövdesi. Gönderim pages/project-category/index.js tarafından devralınır. --}}
<form id="category-form" data-id="{{ $category?->id }}">
    <x-admin::form.input name="name" label="Kategori Adı" required :value="$category?->name"
        placeholder="Örn. Kurumsal Web Sitesi" />

    <x-admin::form.input name="slug" help="common.slug" label="Kısa Ad (slug)" :value="$category?->slug"
        placeholder="Boş bırakılırsa addan üretilir" />

    <x-admin::form.textarea name="description" label="Açıklama" rows="3" :value="$category?->description"
        class="h-[90px]" placeholder="Bu kategorideki işleri tanımlayan kısa metin" />

    {{-- Sıra formdan girilmez: yeni kayıt en sona eklenir, sırayı değiştirmek
         için liste sayfasındaki "Sıralama Modu" kullanılır. --}}
    <x-admin::form.switch name="is_active" help="common.active" label="Aktif"
        :checked="$category?->is_active ?? true" />

    <details class="mb-[20px] md:mb-[25px] rounded-md border border-gray-100 dark:border-[#172036]">
        <summary class="cursor-pointer select-none px-[15px] py-[12px] font-medium text-black dark:text-white flex items-center gap-[8px]">
            <i class="material-symbols-outlined !text-[19px] text-primary-500">travel_explore</i>
            SEO Ayarları
        </summary>
        <div class="px-[15px] pb-[15px] pt-[5px] border-t border-gray-100 dark:border-[#172036]">
            <x-admin::form.seo :model="$category" titleSource="name" descriptionSource="description"
                path="neler-yaptik/kategori" wrapper="" />
        </div>
    </details>

    <x-admin::form.actions :submit="$category ? 'Güncelle' : 'Ekle'" />
</form>
