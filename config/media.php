<?php

/*
| Medya kütüphanesi ayarları.
|
| 'presets' kırpma bileşeninin okuduğu boyutlardır. <x-admin::form.image>
| bileşenine preset="blog.cover" verildiğinde kırpma modalı bu orana kilitlenir
| ve çıktı tam bu boyutta üretilir. Preset tanımlı değilse kırpma modalı açılmaz.
*/

return [

    'disk' => 'public',
    'directory' => 'uploads',

    // Imagick daha iyi kalite verir; yoksa 'gd' kullan.
    'driver' => extension_loaded('imagick') ? 'imagick' : 'gd',

    'quality' => 85,

    /*
    | Depolama çubuğunun paydası (bayt). Sunucuda zorlanan bir sınır DEĞİLDİR
    | — yalnızca medya kütüphanesi sidebar'ındaki "X / Y kullanılıyor"
    | göstergesini ölçeklendirir. Barındırma paketinin alanına göre ayarla.
    */
    'quota' => 5 * 1024 * 1024 * 1024, // 5 GB
    'max_size' => 8192, // KB
    'accepts' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'mp4', 'webm'],

    /*
    | Uzantı bazlı boyut sınırı (KB) — `max_size`'ı ezer. Video bir görselden
    | kat kat büyüktür; ortak sınır ya videoyu imkansız kılar ya da görseller
    | için fazla gevşek olur.
    |
    | DİKKAT: Buradaki sayı PHP'nin `upload_max_filesize` ve `post_max_size`
    | değerlerinden büyükse istek Laravel'e hiç ulaşmaz — sunucuda o ikisi de
    | yükseltilmelidir, yoksa kullanıcı boş bir hata görür.
    */
    'max_size_by_extension' => [
        'mp4' => 65536,  // 64 MB
        'webm' => 65536,
    ],

    /*
    | Her işlenmiş görselden otomatik türetilen boyutlar.
    | fit: 'cover' tam boyuta kırpar, 'scale' oranı koruyup küçültür.
    */
    'conversions' => [
        'thumb' => ['width' => 400, 'height' => 400, 'fit' => 'cover'],
        'medium' => ['width' => 1000, 'height' => null, 'fit' => 'scale'],
    ],

    /*
    | Modül/alan bazlı kırpma boyutları. Anahtar serbesttir; önerilen
    | kalıp <modül>.<alan>. İleride bu liste panelden yönetilecek.
    */
    'presets' => [
        'blog.cover' => ['width' => 1200, 'height' => 630, 'label' => 'Blog Kapak Görseli'],
        'page.cover' => ['width' => 1920, 'height' => 600, 'label' => 'Sayfa Üst Görseli'],
        'service.icon' => ['width' => 256, 'height' => 256, 'label' => 'Hizmet İkonu'],
        'service.cover' => ['width' => 800, 'height' => 500, 'label' => 'Hizmet Kapak Görseli'],
        'project.cover' => ['width' => 1200, 'height' => 750, 'label' => 'Proje Kapak Görseli'],
        'project.gallery' => ['width' => 1600, 'height' => 1000, 'label' => 'Proje Galeri Görseli'],
        'social.icon' => ['width' => 256, 'height' => 256, 'label' => 'Sosyal Medya İkonu'],
        'testimonial.photo' => ['width' => 200, 'height' => 200, 'label' => 'Müşteri Yorumu Fotoğrafı'],
        'slider.image' => ['width' => 1920, 'height' => 800, 'label' => 'Slider Görseli'],
        'hero.gallery' => ['width' => 274, 'height' => 40, 'label' => 'Tanıtım Alanı Görseli'],
        'hero.background' => ['width' => 2160, 'height' => 1193, 'label' => 'Tanıtım Alanı Arka Planı'],
        'popup.image' => ['width' => 800, 'height' => 450, 'label' => 'Açılır Pencere Görseli'],
        'user.avatar' => ['width' => 300, 'height' => 300, 'label' => 'Profil Fotoğrafı'],
        // Sosyal paylaşım görseli — <x-admin::form.seo> bileşeni kullanır.
        'seo.og' => ['width' => 1200, 'height' => 630, 'label' => 'Paylaşım Görseli'],
    ],

];
