@php($ctaCompany = \App\Support\Settings::group('company'))

<section class="blog-detail-cta sp sec-bg1" aria-label="İletişim çağrısı">
    <div class="container">
        <div class="blog-detail-cta__inner text-center">
            <h2>Bu konuda destek almak ister misiniz?</h2>
            <p>
                {{ $ctaCompany['name'] ?: config('app.name') }} olarak hedeflerinize uygun web ve dijital çözümler
                sunuyoruz. Ücretsiz ön görüşme için bize ulaşın.
            </p>
            <a class="ui-btn ui-btn--solid" href="{{ route('iletisim') }}">
                Bize Ulaşın <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
