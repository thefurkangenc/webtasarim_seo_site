{{--
    Liste ve detay sayfalarının altındaki çağrı bloğu. Metin firma adıyla
    kişiselleşir, hedef her zaman iletişim sayfasıdır — projeler bölümüne
    ikinci bir form koymuyoruz, lead altyapısı tek formdan yürüyor.
--}}
@php($ctaCompany = \App\Support\Settings::group('company'))

<div class="cta2 sp sec-bg1">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 m-auto text-center">
                <div class="home-refs__head text-center">

                    <span class="sub-title">
                        <img style="width: 20px; height: 20px; margin-right: 5px;"
                            src="{{ asset('assets/img/icons/icon.png') }}" alt="Gaziantep Web Tasarım Ajansı Blog">
                        Sıradaki proje sizin olsun
                    </span>

                    <h2 id="home-refs-title" class="text-anime-style-3">Sıradaki proje sizin olsun</h2>
                    <p class="home-refs__lead">
                        {{ $ctaCompany['name'] ?: config('app.name') }} ekibi olarak ihtiyaçlarınızı dinliyor, hedeflerinizi birlikte değerlendirerek size uygun yol haritasını oluşturuyoruz. Projenizi konuşmak ve detayları birlikte değerlendirmek için hemen bizimle iletişime geçin.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
