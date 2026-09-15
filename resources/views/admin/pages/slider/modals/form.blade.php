{{-- AJAX modal gövdesi. Gönderim pages/slider/index.js tarafından devralınır. --}}
<form id="slider-form" data-id="{{ $slider?->id }}" enctype="multipart/form-data">
    <div class="flex items-center justify-end mb-[15px]">
        <x-admin::form.switch name="is_active" help="slider.is_active" label="Yayında"
            :checked="$slider?->is_active ?? true" wrapper="" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-[20px] md:gap-[25px]">
        <div class="lg:col-span-2">
            <x-admin::form.input name="slogan" help="slider.slogan" label="Slogan" :value="$slider?->slogan"
                placeholder="Örn. YENİ SEZON" />

            <x-admin::form.input name="title" help="slider.title" label="Başlık" required :value="$slider?->title" />

            <x-admin::form.textarea name="description" help="slider.description" label="Açıklama" rows="5"
                :value="$slider?->description" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[20px] md:gap-[25px]">
                <x-admin::form.input name="button_text" help="slider.button_text" label="Buton Metni"
                    :value="$slider?->button_text" placeholder="Örn. Detaylı Bilgi" />

                <x-admin::form.input name="button_url" help="slider.button_url" label="Buton Adresi"
                    :value="$slider?->button_url" placeholder="/iletisim ya da https://..." />
            </div>
        </div>

        <div>
            <x-admin::form.image name="desktop_media_id" help="slider.desktop_media_id" label="Masaüstü Görseli"
                required preset="slider.desktop" :media="$slider?->getFirstMedia('desktop')" />

            <x-admin::form.image name="mobile_media_id" help="slider.mobile_media_id" label="Mobil Görseli"
                preset="slider.mobile" :media="$slider?->getFirstMedia('mobile')" />
        </div>
    </div>

    {{-- Sıra artık formdan girilmez: yeni kayıt otomatik en sona eklenir,
         sırayı değiştirmek için liste sayfasındaki "Sıralama Modu" kullanılır. --}}

    <x-admin::form.actions :submit="$slider ? 'Güncelle' : 'Ekle'" />
</form>
