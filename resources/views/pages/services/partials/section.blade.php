{{--
    Hizmet kartları — ana sayfa ve hizmet detayı aynı markup'ı kullanır.
    $services  Collection<Service>
--}}
<div class="service6 sp sec-bg5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="heading6 _mt-50">
                    <span class="sub-title">Hizmetler</span>
                    <h2 class="text-anime-style-3">İşinizi Büyütecek Hizmetler</h2>
                    <p class="mt-16">Web tasarım, SEO ve dijital pazarlama ile markanızı arama sonuçlarında öne çıkarıyoruz. İhtiyacınıza uygun çözümlerle daha fazla görünürlük, trafik ve müşteri hedefliyoruz.</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="buttons text-end sm:text-start md:text-start sm:mt-20 md:mt-20" data-aos="fade-left"
                    data-aos-duration="1100">
                    <a href="{{ route('hizmetler') }}" class="ui-btn ui-btn--solid">
                        Tüm Hizmetler <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        @if ($services->isEmpty())
            <div class="row mt-30">
                <div class="col-lg-8 m-auto text-center">
                    <p>Henüz yayınlanmış bir hizmet bulunmuyor.</p>
                </div>
            </div>
        @else
            <div class="row mt-30">
                @foreach ($services as $item)
                    @php
                        $serviceGeneric = $item->renderGeneric();
                        $serviceCover = $item->getFirstMedia('cover');
                    @endphp
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="900">
                        <div class="service6-box mt-30">
                            @if ($serviceCover)
                                <div class="thumb">
                                    <a href="{{ route('hizmetler.show', $item->slug) }}">
                                        <img src="{{ $serviceCover->url() }}"
                                            alt="{{ $serviceGeneric['title'] }}">
                                    </a>
                                </div>
                            @endif
                            <div class="content heading6">
                                <h4><a
                                        href="{{ route('hizmetler.show', $item->slug) }}">{{ $serviceGeneric['title'] }}</a>
                                </h4>
                                <p class="mt-16">
                                    {{ $serviceGeneric['excerpt'] ?: \Illuminate\Support\Str::limit(strip_tags((string) $serviceGeneric['content']), 120) }}
                                </p>
                                <a href="{{ route('hizmetler.show', $item->slug) }}" class="learn">Detaylı
                                    Bilgi <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span
                                        class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
