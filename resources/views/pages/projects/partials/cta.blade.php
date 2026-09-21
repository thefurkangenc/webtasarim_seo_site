{{--
    Liste ve detay sayfalarının altındaki çağrı bloğu. Metin firma adıyla
    kişiselleşir, hedef her zaman iletişim sayfasıdır — projeler bölümüne
    ikinci bir form koymuyoruz, lead altyapısı tek formdan yürüyor.
--}}
@php($ctaCompany = \App\Support\Settings::group('company'))

<div class="cta2 sp sec-bg1">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 m-auto text-center">
                <div class="heading2">
                    <h2>Sıradaki proje sizin olsun</h2>
                    <p class="mt-16">
                        Benzer bir işe ihtiyacınız varsa {{ $ctaCompany['name'] ?: config('app.name') }} ekibi
                        hedeflerinizi dinleyip yol haritasını birlikte çıkarır.
                    </p>
                    <div class="button mt-30">
                        <a class="ui-btn ui-btn--solid" href="{{ route('iletisim') }}">
                            Bize Ulaşın <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
