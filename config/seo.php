<?php

/*
| SEO skorlama kuralları. Tek kaynak — hem PHP (App\Services\Seo\SeoAnalyzer)
| hem tarayıcı (core/seo-analyzer.js, <x-admin::form.seo>'da data-seo-rules
| olarak JSON aktarılır) bu eşikleri kullanır. Biri değişirse ikisi de.
*/

return [

    // Meta başlık karakter aralığı (Google ~600px ≈ 60 karakter).
    'title' => ['min' => 30, 'max' => 60],

    // Meta açıklama karakter aralığı.
    'description' => ['min' => 70, 'max' => 160],

    // Anahtar kelime yoğunluğu (%). Altı "az", üstü "keyword stuffing".
    'density' => ['min' => 0.5, 'max' => 3.0],

    // İçerik için asgari kelime sayısı — modül tipine göre.
    'min_words' => [
        'default' => 300,
        'page' => 250,
        'service' => 200,
    ],

    // Bir paragraf bu kelime sayısını aşarsa "uzun paragraf".
    'paragraph_max_words' => 150,

    // Bir cümle bu kelime sayısını aşarsa "uzun cümle".
    'sentence_long_words' => 20,

    // Uzun cümlelerin toplam içindeki payı bu %'yi aşarsa kontrol düşer.
    'sentence_long_ratio' => 25,

    // İki alt başlık arasında bu kadar kelimeden fazla varsa "bölümlenmemiş".
    'section_max_words' => 300,

    // İlk paragrafta anahtar kelime aranırken bakılacak ilk N kelime.
    'first_paragraph_words' => 120,

    /*
    | Ateşman okunabilirlik bantları (yüksek = kolay). Puan:
    |   198.825 − 40.175·(hece/kelime) − 2.610·(kelime/cümle)
    | Türkçede hece sayısı ≈ sesli harf sayısı.
    */
    'readability_bands' => [
        ['min' => 90, 'label' => 'Çok kolay'],
        ['min' => 80, 'label' => 'Kolay'],
        ['min' => 70, 'label' => 'Orta-kolay'],
        ['min' => 60, 'label' => 'Orta'],
        ['min' => 50, 'label' => 'Orta-zor'],
        ['min' => 40, 'label' => 'Zor'],
        ['min' => 0, 'label' => 'Çok zor'],
    ],

    // Okunabilirlik kontrolü: bu puanın altı "bad", ortası "ok".
    'readability' => ['ok' => 50, 'good' => 60],

    // Skor eşikleri: <= bad kötü (kırmızı), <= ok orta (turuncu), üstü iyi (yeşil).
    'grade' => ['bad' => 40, 'ok' => 70],

    /*
    | Her kontrolün skora ağırlığı. "good" tam, "ok" yarım, "bad" 0 puan alır;
    | "na" (uygulanamaz, örn. odak kelime yokken) skordan tamamen çıkar.
    */
    'weights' => [
        'keyword_set' => 2,
        'keyword_in_title' => 3,
        'keyword_in_description' => 2,
        'keyword_in_slug' => 2,
        'keyword_in_intro' => 2,
        'keyword_in_subheading' => 2,
        'keyword_in_image_alt' => 1,
        'keyword_density' => 3,
        'title_length' => 3,
        'description_length' => 3,
        'content_length' => 3,
        'images_have_alt' => 2,
        'internal_links' => 2,
        'outbound_links' => 1,
        'paragraph_length' => 2,
        'sentence_length' => 2,
        'subheading_distribution' => 2,
        'readability' => 3,
    ],
];
