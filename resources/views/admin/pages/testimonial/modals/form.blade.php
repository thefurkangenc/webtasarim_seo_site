{{-- AJAX modal gövdesi. Gönderim pages/testimonial/index.js tarafından devralınır. --}}
<form id="testimonial-form" data-id="{{ $testimonial?->id }}" enctype="multipart/form-data">
    <x-admin::form.image name="photo_media_id" help="testimonial.photo_media_id" label="Fotoğraf" preset="testimonial.photo"
        :media="$testimonial?->getFirstMedia('photo')" />

    <x-admin::form.input name="name" help="testimonial.name" label="İsim" required :value="$testimonial?->name" />

    <x-admin::form.input name="title" help="testimonial.title" label="Unvan" :value="$testimonial?->title"
        placeholder="Örn. CEO, BrightEdge Media" />

    <x-admin::form.textarea name="content" help="testimonial.content" label="Yorum" rows="4" required
        :value="$testimonial?->content" />

    <x-admin::form.select name="rating" help="testimonial.rating" label="Puan" required :placeholder="null"
        :options="[1 => '1 Yıldız', 2 => '2 Yıldız', 3 => '3 Yıldız', 4 => '4 Yıldız', 5 => '5 Yıldız']"
        :value="$testimonial?->rating ?? 5" />

    {{-- Sıra artık formdan girilmez: yeni kayıt otomatik en sona eklenir,
         sırayı değiştirmek için liste sayfasındaki "Sıralama Modu" kullanılır. --}}

    <x-admin::form.switch name="is_active" help="testimonial.is_active" label="Yayında"
        :checked="$testimonial?->is_active ?? true" />

    <x-admin::form.actions :submit="$testimonial ? 'Güncelle' : 'Ekle'" />
</form>
