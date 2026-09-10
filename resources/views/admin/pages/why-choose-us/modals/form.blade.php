{{-- AJAX modal gövdesi. Gönderim pages/why-choose-us/index.js tarafından devralınır. --}}
<form id="why-choose-us-form" data-id="{{ $whyChooseUs?->id }}">
    <x-admin::form.input name="title" help="why_choose_us.title" label="Başlık" required :value="$whyChooseUs?->title" />

    <x-admin::form.textarea name="description" help="why_choose_us.description" label="Açıklama" rows="5" required :value="$whyChooseUs?->description" />

    {{-- Sıra artık formdan girilmez: yeni kayıt otomatik en sona eklenir,
         sırayı değiştirmek için liste sayfasındaki "Sıralama Modu" kullanılır. --}}

    <x-admin::form.actions :submit="$whyChooseUs ? 'Güncelle' : 'Ekle'" />
</form>
