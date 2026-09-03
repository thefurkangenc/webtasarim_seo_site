{{-- AJAX modal gövdesi. Gönderim pages/blog-category/index.js tarafından devralınır. --}}
<form id="category-form" data-id="{{ $category?->id }}">
    <x-admin::form.input name="name" label="Kategori Adı" required :value="$category?->name" />

    <x-admin::form.input name="slug" label="Kısa Ad (slug)" :value="$category?->slug"
        placeholder="Boş bırakılırsa addan üretilir" />

    <x-admin::form.textarea name="description" label="Açıklama" rows="3"
        :value="$category?->description" class="h-[90px]" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
        <x-admin::form.input name="sort_order" type="number" label="Sıra"
            :value="$category?->sort_order ?? 0" min="0" wrapper="mb-[20px] md:mb-[25px]" />

        <div class="flex items-end pb-[8px]">
            <x-admin::form.switch name="is_active" label="Aktif"
                :checked="$category?->is_active ?? true" wrapper="" />
        </div>
    </div>

    <details class="mb-[20px] md:mb-[25px] rounded-md border border-gray-100 dark:border-[#172036]">
        <summary class="cursor-pointer select-none px-[15px] py-[12px] font-medium text-black dark:text-white flex items-center gap-[8px]">
            <i class="material-symbols-outlined !text-[19px] text-primary-500">travel_explore</i>
            SEO Ayarları
        </summary>
        <div class="px-[15px] pb-[15px] pt-[5px] border-t border-gray-100 dark:border-[#172036]">
            <x-admin::form.seo :model="$category" titleSource="name" descriptionSource="description"
                path="blog/kategori" wrapper="" />
        </div>
    </details>

    <x-admin::form.actions :submit="$category ? 'Güncelle' : 'Ekle'" />
</form>
