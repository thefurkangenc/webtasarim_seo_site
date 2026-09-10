{{-- AJAX modal gövdesi. Gönderim pages/service-region/index.js tarafından devralınır. --}}
<form id="region-form" data-id="{{ $region?->id }}">
    <div class="mb-[20px] md:mb-[25px] rounded-md bg-gray-50 dark:bg-[#15203c] px-[15px] py-[12px] flex items-center gap-[8px]">
        <i class="material-symbols-outlined !text-[19px] text-primary-500">account_tree</i>
        <span class="text-sm text-black dark:text-white">
            @if ($parent)
                Üst bölge: <span class="font-medium">{{ $parent->path }}</span>
            @else
                Üst bölge yok — bu kayıt bir <span class="font-medium">il</span> olarak açılır.
            @endif
        </span>
    </div>

    {{-- Üst bölge normalde kırılımdan gelir (yukarıdaki bilgi kutusu bunu
         gösterir); bu select aynı değerle önceden dolu gelir ama kullanıcı
         hiç kırılıma inmeden de bölgeyi doğrudan buradan seçebilir — bir
         güvenlik/kolaylık ağı. Düzenlemede taşıma desteklenmiyor, bu yüzden
         yalnızca yeni kayıtta görünür. --}}
    @unless ($region)
        <x-admin::form.select name="parent_id" help="service_region.parent_id" label="Üst Bölge"
            :options="$parentOptions" :value="$parent?->id"
            placeholder="Üst bölge yok — il olarak eklenir" />
    @endunless

    <x-admin::form.input name="name" help="service_region.name" label="Bölge Adı" required :value="$region?->name"
        placeholder="Örn. Şahinbey" />

    <x-admin::form.input name="slug" help="service_region.slug" label="Kısa Ad (slug)" :value="$region?->slug"
        placeholder="Boş bırakılırsa addan üretilir" />

    <x-admin::form.textarea name="description" help="service_region.description" label="Bölgeye Özel Metin" rows="4"
        :value="$region?->description" class="h-[110px]"
        placeholder="Bu bölgenin hizmet sayfalarına eklenecek özgün metin. Aynı hizmetin farklı bölge sayfaları birbirinin kopyası olmasın diye kullanılır." />

    {{-- Sıra formdan girilmez: yeni kayıt bulunduğu seviyenin sonuna eklenir,
         sırayı değiştirmek için liste sayfasındaki "Sıralama Modu" kullanılır. --}}
    <x-admin::form.switch name="is_active" label="Aktif" :checked="$region?->is_active ?? true" />

    <x-admin::form.actions :submit="$region ? 'Güncelle' : 'Ekle'" />
</form>
