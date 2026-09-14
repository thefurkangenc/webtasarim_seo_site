/**
 * Proje detay galerisi — magnific-popup lightbox'ı.
 *
 * Eklenti ve jQuery layout'ta global yükleniyor (scripts.blade.php);
 * main.js yalnızca .play-btn'i iframe tipiyle bağlıyor, galeri tipi
 * burada bağlanır. Galeri yoksa sarmalayıcı hiç basılmadığı için
 * seçici boş döner, hata olmaz.
 */
jQuery(function ($) {
    $('[data-project-gallery]').magnificPopup({
        delegate: 'a[data-gallery-item]',
        type: 'image',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            tPrev: 'Önceki',
            tNext: 'Sonraki',
            tCounter: '%curr% / %total%',
        },
        image: {
            titleSrc: 'data-title',
            tError: 'Görsel yüklenemedi.',
        },
    });
});
