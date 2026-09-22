{{--
    Referans logoları + alt CTA. Ana sayfa ve /referanslar aynı markup'ı kullanır.
    $references  Collection<Reference>
    $showEyebrow  bool — liste sayfasında h1 zaten "Referanslar" olduğu için kapatılır.
--}}
@php
    $showEyebrow = $showEyebrow ?? true;
@endphp

<section class="home-refs sp" aria-labelledby="home-refs-title">
    <div class="container">
        <div class="home-refs__head text-center">

            <span class="sub-title">
                <img style="width: 20px; height: 20px; margin-right: 5px;"
                    src="{{ asset('assets/img/icons/icon.png') }}" alt="Gaziantep Web Tasarım Ajansı Referansları">
                    Referanslar
            </span>

            <h2 id="home-refs-title" class="text-anime-style-3">Bizimle Çalışan Şirketler</h2>
            <p class="home-refs__lead">
                Farklı sektörlerden yüzlerce işletme dijital dönüşümünde bizi tercih etti.
            </p>
        </div>

        @if ($references->isEmpty())
            <p class="home-refs__empty text-center">Henüz yayınlanmış bir referans bulunmuyor.</p>
        @else
            <div class="home-refs__grid">
                @foreach ($references as $reference)
                    @php $referenceLogo = $reference->getFirstMedia('logo'); @endphp
                    @if ($referenceLogo)
                        <div class="home-refs__item">
                            @if (filled($reference->url))
                                <a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer"
                                    title="{{ $reference->name }}">
                                    <img src="{{ $referenceLogo->url('reference.logo') }}"
                                        loading="lazy">
                                </a>
                            @else
                                <img src="{{ $referenceLogo->url('reference.logo') }}"
                                    loading="lazy">
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="home-refs__bar">
            <div class="home-refs__bar-text">
                <p class="home-refs__bar-title">Sıradaki başarı hikayesi sizinki olabilir!</p>
                <p class="home-refs__bar-note">Ücretsiz ön görüşme · Size özel teklif · Çözüm Odaklı Yaklaşım</p>
            </div>
            <a href="{{ route('iletisim') }}" class="ui-btn ui-btn--solid">Detayları Konuşalım <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>
