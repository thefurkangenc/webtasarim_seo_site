{{-- AJAX modal gövdesi. Gönderim pages/faq/index.js tarafından devralınır. --}}
<form id="faq-form" data-id="{{ $faq?->id }}">
    <x-admin::form.input name="question" help="faq.question" label="Soru" required :value="$faq?->question" />

    <x-admin::form.textarea name="answer" help="faq.answer" label="Cevap" rows="5" required :value="$faq?->answer" />

    {{-- Sıra artık formdan girilmez: yeni kayıt otomatik en sona eklenir,
         sırayı değiştirmek için liste sayfasındaki "Sıralama Modu" kullanılır. --}}

    <x-admin::form.switch name="is_active" help="faq.is_active" label="Yayında"
        :checked="$faq?->is_active ?? true" />

    <x-admin::form.actions :submit="$faq ? 'Güncelle' : 'Ekle'" />
</form>
