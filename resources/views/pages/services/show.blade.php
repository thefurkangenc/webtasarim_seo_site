@extends('layout.app')
@section('title', $rendered['title'])
@section('meta_description', (string) $rendered['seo']['description'])
@section('meta_keywords', (string) $rendered['seo']['keywords'])
@section('meta_image', (string) $rendered['seo']['image'])
@section('content')
    @php
        // Bölge sayfasında üst kırılım "hizmetin genel adı" olarak bölgesiz
        // (yer tutucusuz) başlığı gösterir — "Gaziantep Web Tasarım > Gaziantep"
        // gibi tekrarlı görünmesin diye burada ayrıca çözülür.
        $genericTitle = $service->renderGeneric()['title'];
    @endphp

    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto text-center">
                    <div class="inner-main-heading">
                        <h1>{{ $rendered['title'] }}</h1>
                        <div class="breadcrumbs-pages">
                            <ul>
                                <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                                <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('hizmetler') }}">Hizmetler</a></li>
                                @if ($region)
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li><a href="{{ route('hizmetler.show', $service->slug) }}">{{ $genericTitle }}</a></li>
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>{{ $region->name }}</li>
                                @else
                                    <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                                    <li>{{ $rendered['title'] }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== HERO AREA START =====-->

    <!--===== BLOG DETAILS AREA START =====-->

    <div class="blog-details-area sp">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="sidebar-area">

                        <div class="_sidebar-widget _contact quote-widget" data-quote-widget>
                            <h3>Hızlı Teklif Alın</h3>
                            <p class="text-muted" style="margin-bottom: 5px;font-size: 14px;">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quos.
                            </p>
                            <div class="_contact-form quote-step-form-wrap mt-1">
                                <form class="quote-step-form" id="quote-step-form" action="#" novalidate>
                                    <div class="quote-progress-row">
                                        <p class="quote-step-meta"><span data-quote-current>1</span> / 2</p>
                                        <div class="quote-progress" aria-hidden="true">
                                            <span class="quote-progress-fill" data-quote-progress></span>
                                        </div>
                                    </div>

                                    <div class="quote-steps-viewport">
                                        <div class="quote-steps-track" data-quote-track>
                                            <div class="quote-step is-active" data-step="1">
                                                <div class="quote-field">
                                                    <label for="quote-company">Firma Adınız Nedir?</label>
                                                    <input type="text" name="company" id="quote-company"
                                                        placeholder="Örn. Umay Dijital" autocomplete="organization"
                                                        required>
                                                    <p class="quote-error" data-error-for="company" hidden>Firma adını
                                                        yazın.</p>
                                                </div>
                                                <div class="quote-field">
                                                    <label for="quote-service">Hangi Hizmetle İlgileniyorsunuz?</label>
                                                    <select class="quote-step-select" name="service" id="quote-service"
                                                        required>
                                                        <option value="" disabled selected>Hizmet seçin</option>
                                                        <option value="Kurumsal Web Tasarım">Kurumsal Web Tasarım</option>
                                                        <option value="Özel Yazılım Geliştirme">Özel Yazılım Geliştirme
                                                        </option>
                                                        <option value="Arama Motoru (SEO) Optimizasyonu">Arama Motoru (SEO)
                                                            Optimizasyonu</option>
                                                        <option value="Dijital Pazarlama">Dijital Pazarlama</option>
                                                        <option value="Hosting &amp; Barındırma">Hosting &amp; Barındırma
                                                        </option>
                                                        <option value="e-Ticaret Danışmanlığı &amp; Yönetimi">e-Ticaret
                                                            Danışmanlığı &amp; Yönetimi</option>
                                                    </select>
                                                    <p class="quote-error" data-error-for="service" hidden>Bir hizmet seçin.
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="quote-step" data-step="2">
                                                <div class="quote-field">
                                                    <label for="quote-phone">Telefon Numaranız Nedir?</label>
                                                    <input type="tel" name="phone" id="quote-phone"
                                                        inputmode="numeric" placeholder="0 (___) ___ __ __"
                                                        autocomplete="tel" maxlength="19" required>
                                                    <p class="quote-error" data-error-for="phone" hidden>Geçerli bir telefon
                                                        numarası yazın.</p>
                                                </div>
                                                <div class="quote-field quote-field-notes">
                                                    <label for="quote-notes">Dilerseniz Buraya Ekstra Notlarınızı
                                                        Yazabilirsiniz.</label>
                                                    <textarea name="notes" id="quote-notes" rows="3" placeholder="Projeniz hakkında kısaca yazabilirsiniz."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="quote-nav" data-quote-nav>
                                        <button type="button" class="quote-btn-back" data-quote-back hidden>
                                            <i class="fa-solid fa-arrow-left"></i> Geri
                                        </button>
                                        <button type="button" class="theme-btn3 quote-btn-next" data-quote-next>
                                            İleri <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span
                                                class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
                                        </button>
                                        <button type="submit" class="theme-btn3 quote-btn-submit" data-quote-submit hidden>
                                            Talebimi Gönder <span class="arrow1"><i
                                                    class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i
                                                    class="fa-solid fa-arrow-right"></i></span>
                                        </button>
                                    </div>
                                </form>

                                <div class="quote-success" data-quote-success hidden role="status" aria-live="polite">
                                    <div class="quote-success-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <h4>Talebiniz alındı</h4>
                                    <p>En kısa sürede sizinle iletişime geçeceğiz.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-6">
                    <div class="blog-details-content ml-30 md:ml-0 sm:ml-0">

                        <div class="heading2 mt-24">
                            <h3>{{ $rendered['title'] }}</h3>
                            @if (filled($rendered['excerpt']))
                                <p class="mt-16">{{ $rendered['excerpt'] }}</p>
                            @endif
                        </div>


                        @php($cover = $service->getFirstMedia('cover'))
                        @if ($cover)
                            <article>
                                <div class="details-content">
                                    <div class="image">
                                        <img class="w-full" src="{{ $cover->url('medium') }}"
                                            alt="{{ $rendered['title'] }}">
                                    </div>
                                </div>
                            </article>
                        @endif

                        <article>
                            <div class="details-content">


                                @if (filled($rendered['content']))
                                    <div class="heading2 mt-24">
                                        {!! $rendered['content'] !!}
                                    </div>
                                @endif

                            </div>
                        </article>



                        <div class="details-border"></div>



                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="sidebar-area position-relative top-0">


                        @if ($service->regions->isNotEmpty())
                            <div class="_sidebar-widget _list">
                                <h3>Bu Hizmeti Sunduğumuz Bölgeler</h3>
                                <p class="text-muted" style="margin-bottom: 15px;font-size: 14px;">
                                    Bulunduğunuz bölgeye göre {{ $genericTitle }} hizmetimiz hakkında daha fazla bilgi
                                    alın.
                                </p>
                                <div class="sidebar-list">
                                    <ul>
                                        @foreach ($service->regions as $serviceRegion)
                                            <li>
                                                <a href="{{ route('hizmetler.show-region', [$service->slug, $serviceRegion->slug]) }}"
                                                    @class(['active' => $region?->id === $serviceRegion->id])>
                                                    {{ $serviceRegion->path ?: $serviceRegion->name }}
                                                    <span><i class="fa-solid fa-angle-right"></i></span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif




                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--===== BLOG DETAILS AREA END =====-->

    <!--===== CTA AREA STARTS =======-->
    <div class="cta-section-area others-cta sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto">
                    <div class="cta-header-area text-center sp4 white-heading">
                        <h2>Competitor Analysis</h2>
                        <p class="mt-16">Find the keywords your competitors rank for and analyze their <br
                                class="d-lg-block d-none"> data insights to uncover their SEO strategy in one click</p>
                        <div class="space40"></div>
                        <div class="form-area">
                            <form>
                                <div class="input-area">
                                    <span><i class="fa-solid fa-link"></i></span>
                                    <input type="text" placeholder="https:// yoursite.com">
                                </div>

                                <div class="input-area">
                                    <span><i class="fa-regular fa-envelope"></i></span>
                                    <input type="text" placeholder="youremail@domain.com">
                                </div>
                                <div class="btn-area">
                                    <button class="theme-btn3" type="submit">Contact Us <span class="arrow1"><i
                                                class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i
                                                class="fa-solid fa-arrow-right"></i></span></button>
                                </div>
                            </form>
                        </div>
                        <ul>
                            <li>Try:</li>
                            <li><a href="#">Marketing</a></li>
                            <li><a href="#">Laptop</a></li>
                            <li><a href="#">iPhone</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--===== CTA AREA ENDS =======-->

    <!-- analysis-area-start -->
    <section class="analysis-area sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-center">
                    <div class="heading2">
                        <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                src="assets/img/icons/span3.svg" alt=""> WEBSITE ANALYSIS </span>
                        <h2 class="text-anime-style-3">Conduct Website Audience Analysis and Explore Its Geography</h2>
                    </div>
                </div>
            </div>
            <div class="space60"></div>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <div class="services-seo">
                        <div class="services-seo-scroll">
                            <div class="services-seo-head">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-5 col-5">
                                        <div class="services-seo-heading">
                                            <h4 class="services-seo-heading-title">
                                                <input id="remeber" type="checkbox">
                                                <label for="remeber">Blanking</label>
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="col-xl-8 col-lg-7 col-7">
                                        <div class="services-seo-catagory">
                                            <div class="row">
                                                <div class="col-lg-3 col-3">
                                                    <div class="services-seo-heading-item services-seo-catagory-one">
                                                        <span>Score</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-3">
                                                    <div class="services-seo-heading-item services-seo-catagory-two">
                                                        <span>Keyword</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-3">
                                                    <div class="services-seo-heading-item services-seo-catagory-three">
                                                        <span>Domain</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-3">
                                                    <div class="services-seo-heading-item services-seo-catagory-four">
                                                        <div class="rank">
                                                            <span>Rank
                                                                <i class="fa-light fa-angle-up"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="services-seo-info">
                                <div class="services-seo-item">
                                    <div class="row align-items-center">
                                        <div class="col-xl-4 col-lg-5 col-5">
                                            <div class="services-seo-link d-flex">
                                                <div class="services-seo-link-check">
                                                    <input id="seo-link-check" type="checkbox">
                                                    <label for="seo-link-check">WOG PRIDE on the app store</label>
                                                    <span><a
                                                            href="#">https://www.daraz.com/gameing-laptops/</a></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-8 col-lg-7 col-7">
                                            <div class="services-seo-catagory">
                                                <div class="row">
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-one">
                                                            <span>86</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-two">
                                                            <span>Laptop</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-three">
                                                            <span><a href="#">daraz.com</a></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-four d-flex align-items-center">
                                                            <div class="stable-rank"><span>4</span></div>
                                                            <div class="incridable-rank">
                                                                <i class="fa-solid fa-angle-up"></i>
                                                                <span>1</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="services-seo-item">
                                    <div class="row align-items-center">
                                        <div class="col-xl-4 col-lg-5 col-5">
                                            <div class="services-seo-link d-flex">
                                                <div class="services-seo-link-check">
                                                    <input id="seo-link-check-2" type="checkbox">
                                                    <label for="seo-link-check-2">SEO PRIDE on the app store</label>
                                                    <span><a
                                                            href="#">https://www.daraz.com/gameing-laptops/</a></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-8 col-lg-7 col-7">
                                            <div class="services-seo-catagory">
                                                <div class="row">
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-one">
                                                            <span>105</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-two">
                                                            <span>Laptop</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-three">
                                                            <span><a href="#">daraz.com</a></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-four d-flex align-items-center">
                                                            <div class="stable-rank"><span>0</span></div>
                                                            <div class="incridable-rank">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="services-seo-item">
                                    <div class="row align-items-center">
                                        <div class="col-xl-4 col-lg-5 col-5">
                                            <div class="services-seo-link d-flex">
                                                <div class="services-seo-link-check">
                                                    <input id="seo-link-check-3" type="checkbox">
                                                    <label for="seo-link-check-3">PRIDE on the app store</label>
                                                    <span><a
                                                            href="#">https://www.daraz.com/gameing-laptops/</a></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-8 col-lg-7 col-7">
                                            <div class="services-seo-catagory">
                                                <div class="row">
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-one">
                                                            <span>42</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-two">
                                                            <span>Laptop</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-three">
                                                            <span><a href="#">daraz.com</a></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-four d-flex align-items-center">
                                                            <div class="stable-rank"><span>3</span></div>
                                                            <div class="incridable-rank incridable-rank-y">
                                                                <i class="fa-solid fa-angle-up"></i>
                                                                <span>2</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="services-seo-item">
                                    <div class="row align-items-center">
                                        <div class="col-xl-4 col-lg-5 col-5">
                                            <div class="services-seo-link d-flex">
                                                <div class="services-seo-link-check">
                                                    <input id="seo-link-check-4" type="checkbox">
                                                    <label for="seo-link-check-4">WOG on the Online store</label>
                                                    <span><a
                                                            href="#">https://www.daraz.com/gameing-laptops/</a></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-8 col-lg-7 col-7">
                                            <div class="services-seo-catagory">
                                                <div class="row">
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-one">
                                                            <span>06</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div class="services-seo-catagory-item services-seo-catagory-two">
                                                            <span>Laptop</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-three">
                                                            <span><a href="#">daraz.com</a></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-3">
                                                        <div
                                                            class="services-seo-catagory-item services-seo-catagory-four d-flex align-items-center">
                                                            <div class="stable-rank"><span>5</span></div>
                                                            <div class="incridable-rank">
                                                                <i class="fa-solid fa-angle-up"></i>
                                                                <span>1</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- analysis-area-end -->

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/services/show.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/pages/services/show.js') }}"></script>
@endpush
