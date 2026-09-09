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

                        @if ($service->regions->isNotEmpty())
                            <div class="_sidebar-widget _list">
                                <h3>Bu Hizmeti Sunduğumuz Bölgeler</h3>
                                <p class="text-muted" style="margin-bottom: 15px;font-size: 14px;">
                                    Bulunduğunuz bölgeye göre {{ $genericTitle }} hizmetimiz hakkında daha fazla bilgi alın.
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

                <!-- Main Content -->
                <div class="col-lg-6">
                    <div class="blog-details-content ml-30 md:ml-0 sm:ml-0">
                        @php($cover = $service->getFirstMedia('cover'))
                        @if ($cover)
                            <article>
                                <div class="details-content">
                                    <div class="image">
                                        <img class="w-full" src="{{ $cover->url('medium') }}" alt="{{ $rendered['title'] }}">
                                    </div>
                                </div>
                            </article>
                        @endif

                        <article>
                            <div class="details-content">
                                <div class="heading2 mt-24">
                                    <h3>{{ $rendered['title'] }}</h3>
                                    @if (filled($rendered['excerpt']))
                                        <p class="mt-16">{{ $rendered['excerpt'] }}</p>
                                    @endif
                                </div>

                                @if (filled($rendered['content']))
                                    <div class="heading2 mt-24">
                                        {!! $rendered['content'] !!}
                                    </div>
                                @endif

                                <div class="heading2 mt-40">
                                    <h3>What we Offer </h3>
                                </div>
                                <div class="row pt-10">
                                    <div class="col-md-6">
                                        <div class="details-content-text-box heading2 mt-30">
                                            <h4><a href="#">Personalized Itineraries</a></h4>
                                            <p class="mt-12">Tailored travel plans that match your interests and
                                                preferences.</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="details-content-text-box heading2 mt-30">
                                            <h4><a href="#">Destination Insights</a></h4>
                                            <p class="mt-12">In-depth information about attractions, local experiences, and
                                                must-visit spots.</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="details-content-text-box heading2 mt-30">
                                            <h4><a href="#">Social Media Advertising</a></h4>
                                            <p class="mt-12">Leverage platforms like Facebook, Instagram, LinkedIn, and
                                                Twitter to reach your ideal audience.</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="details-content-text-box heading2 mt-30">
                                            <h4><a href="#">Cultural & Safety Tips</a></h4>
                                            <p class="mt-12">Essential guidelines to help you navigate different cultures
                                                and travel safely.</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="details-content-text-box heading2 mt-30">
                                            <h4><a href="#">Local Dining & Activities</a></h4>
                                            <p class="mt-12">Discover the best restaurants, nightlife, and adventure
                                                opportunities.</p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="details-content-text-box heading2 mt-30">
                                            <h4><a href="#">Visa & Travel Documentation </a></h4>
                                            <p class="mt-12"> Guidance on visa applications, travel insurance, and
                                                essential paperwork.</p>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </article>

                        <article>
                            <div class="details-content mt-40">
                                <div class="heading2">
                                    <h3>Why Choose SEOX for Travel Guidance?</h3>
                                    <p class=" mt-16">Our travel specialists offer in-depth knowledge of destinations
                                        worldwide. Every itinerary is customized to suit your interests and travel style. We
                                        go beyond tourist hotspots to bring you authentic experiences. From flights to
                                        accommodations, we help organize every aspect of your trip. Travel with confidence
                                        knowing expert help is just a call away. </p>
                                </div>

                                <div class="heading2 mt-40">
                                    <h3>Our Travel Guide Process</h3>
                                    <p class=" mt-16">we make travel planning effortless and enriching with our expert
                                        travel guidance. Whether you're seeking an adventurous getaway, a cultural
                                        exploration, or a relaxing retreat, our travel specialists provide personalized
                                        itineraries, essential travel tips, and local insights to enhance your journey. We
                                        help you discover hidden gems, navigate different cultures, and ensure a hassle-free
                                        experience from start to finish. With a focus on seamless planning, safety, and
                                        unique experiences, we turn your travel dreams into reality, allowing you to explore
                                        with confidence and make unforgettable memories.</p>
                                </div>


                                <div class="service-details-check-list heading2 mt-30">
                                    <div class="check-icon">
                                        <span><i class="fa-solid fa-check"></i></span>
                                    </div>
                                    <div class="text">
                                        <h4><a href="#">Campaign Strategy Development</a></h4>
                                        <p class="mt-2">We start by researching your business goals, target audience, and
                                            competitors.</p>
                                    </div>
                                </div>

                                <div class="service-details-check-list heading2 mt-30">
                                    <div class="check-icon">
                                        <span><i class="fa-solid fa-check"></i></span>
                                    </div>
                                    <div class="text">
                                        <h4><a href="#">Crafting the Perfect Itinerary</a></h4>
                                        <p class="mt-2">Our experts design an itinerary with must-visit landmarks, hidden
                                            gems, and local favorites, ensuring a well-balanced trip.</p>
                                    </div>
                                </div>

                                <div class="service-details-check-list heading2 mt-30">
                                    <div class="check-icon">
                                        <span><i class="fa-solid fa-check"></i></span>
                                    </div>
                                    <div class="text">
                                        <h4><a href="#">Providing Essential Travel Tips</a></h4>
                                        <p class="mt-2">From cultural etiquette to packing checklists, we equip you with
                                            insights to enhance your travel experience.</p>
                                    </div>
                                </div>

                                <div class="service-details-check-list heading2 mt-30">
                                    <div class="check-icon">
                                        <span><i class="fa-solid fa-check"></i></span>
                                    </div>
                                    <div class="text">
                                        <h4><a href="#">Ongoing Support & Recommendations</a></h4>
                                        <p class="mt-2">Even during your trip, we provide real-time support and updates
                                            to make your journey hassle-free.</p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="image mt-40">
                                            <img class="w-full" src="assets/img/blog/blog-details-image2.png"
                                                alt="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="image mt-40">
                                            <img class="w-full" src="assets/img/blog/blog-details-image3.png"
                                                alt="">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </article>

                        <div class="details-border"></div>

                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="service-details-box1 text-center">
                                    <div class="icon">
                                        <img src="assets/img/icons/service-details-icon1.svg" alt="">
                                    </div>
                                    <div class="heading2 mt-16">
                                        <h4><a href="#">SEO Optimization </a></h4>
                                        <p class="mt-10">Drive organic traffic and improve your online visibility </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="service-details-box1 text-center sm:mt-30">
                                    <div class="icon">
                                        <img src="assets/img/icons/service-details-icon2.svg" alt="">
                                    </div>
                                    <div class="heading2 mt-16">
                                        <h4><a href="#">PPC Advertising </a></h4>
                                        <p class="mt-10">Maximize ROI with targeted PPC campaigns designed </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="service-details-box1 text-center md:mt-30 sm:mt-30">
                                    <div class="icon">
                                        <img src="assets/img/icons/service-details-icon3.svg" alt="">
                                    </div>
                                    <div class="heading2 mt-16">
                                        <h4><a href="#">Content Marketing </a></h4>
                                        <p class="mt-10">content marketing services encompass everything </p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="details-border"></div>

                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="sidebar-area">

                        <div class="_sidebar-widget _buttons" data-sidebar-question>
                            <h3>You Still Have A Question</h3>
                            <p class="mt-16">If you cannot find answer to your question our FAQ, you can always contact us.
                                Web will answer you shortly!</p>
                            <div class="buttons mt-16">
                                <a href="mailto:Infoseoxagency@gmail.com" class="sidebar-btn1"><img
                                        src="assets/img/icons/sidebar-email.png" alt="">
                                    Infoseoxagency@gmail.com</a>
                                <a href="tel:123-456-7890" class="sidebar-btn2"><img
                                        src="assets/img/icons/sidebar-phone.png" alt=""> 123-456-7890</a>
                            </div>
                        </div>

                        <div class="_sidebar-widget _contact quote-widget mt-30" data-quote-widget>
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
                                                        placeholder="Örn. Umay Dijital" autocomplete="organization" required>
                                                    <p class="quote-error" data-error-for="company" hidden>Firma adını yazın.</p>
                                                </div>
                                                <div class="quote-field">
                                                    <label for="quote-service">Hangi Hizmetle İlgileniyorsunuz?</label>
                                                    <select class="quote-step-select" name="service" id="quote-service" required>
                                                        <option value="" disabled selected>Hizmet seçin</option>
                                                        <option value="Kurumsal Web Tasarım">Kurumsal Web Tasarım</option>
                                                        <option value="Özel Yazılım Geliştirme">Özel Yazılım Geliştirme</option>
                                                        <option value="Arama Motoru (SEO) Optimizasyonu">Arama Motoru (SEO) Optimizasyonu</option>
                                                        <option value="Dijital Pazarlama">Dijital Pazarlama</option>
                                                        <option value="Hosting &amp; Barındırma">Hosting &amp; Barındırma</option>
                                                        <option value="e-Ticaret Danışmanlığı &amp; Yönetimi">e-Ticaret Danışmanlığı &amp; Yönetimi</option>
                                                    </select>
                                                    <p class="quote-error" data-error-for="service" hidden>Bir hizmet seçin.</p>
                                                </div>
                                            </div>

                                            <div class="quote-step" data-step="2">
                                                <div class="quote-field">
                                                    <label for="quote-phone">Telefon Numaranız Nedir?</label>
                                                    <input type="tel" name="phone" id="quote-phone" inputmode="numeric"
                                                        placeholder="0 (___) ___ __ __" autocomplete="tel" maxlength="19" required>
                                                    <p class="quote-error" data-error-for="phone" hidden>Geçerli bir telefon numarası yazın.</p>
                                                </div>
                                                <div class="quote-field quote-field-notes">
                                                    <label for="quote-notes">Dilerseniz Buraya Ekstra Notlarınızı Yazabilirsiniz.</label>
                                                    <textarea name="notes" id="quote-notes" rows="3"
                                                        placeholder="Projeniz hakkında kısaca yazabilirsiniz."></textarea>
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
                                            Talebimi Gönder <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span
                                                class="arrow2"><i class="fa-solid fa-arrow-right"></i></span>
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

    <!--===== SERVICE SECTION AREA START =====-->

    <div class="service sp sec-bg1">
        <div class="container">
            <div class="row">
                <div class="col-md-6 m-auto text-center">
                    <div class="heading2">
                        <h2>More Services</h2>
                    </div>
                </div>
            </div>

            <div class="row mt-30">
                <div class="col-lg-3 col-md-6">
                    <div class="service-page-box mt-30">
                        <div class="image">
                            <img src="assets/img/service/service5-image1.png" alt="">
                        </div>
                        <div class="content-area">
                            <div class="num">01</div>
                            <a href="service-details.html" class="arrow"><i class="fa-regular fa-arrow-right"></i></a>
                            <h4><a href="service-details.html">Business Strategy</a></h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="service-page-box mt-30">
                        <div class="image">
                            <img src="assets/img/service/service5-image2.png" alt="">
                        </div>
                        <div class="content-area">
                            <div class="num">02</div>
                            <a href="service-details.html" class="arrow"><i class="fa-regular fa-arrow-right"></i></a>
                            <h4><a href="service-details.html">Business Strategy</a></h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="service-page-box mt-30">
                        <div class="image">
                            <img src="assets/img/service/service5-image3.png" alt="">
                        </div>
                        <div class="content-area">
                            <div class="num">03</div>
                            <a href="service-details.html" class="arrow"><i class="fa-regular fa-arrow-right"></i></a>
                            <h4><a href="service-details.html">Business Strategy</a></h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="service-page-box mt-30">
                        <div class="image">
                            <img src="assets/img/service/service5-image4.png" alt="">
                        </div>
                        <div class="content-area">
                            <div class="num">04</div>
                            <a href="service-details.html" class="arrow"><i class="fa-regular fa-arrow-right"></i></a>
                            <h4><a href="service-details.html">Business Strategy</a></h4>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--===== SERVICE SECTION AREA END =====-->

    <!--===== CONTACT AREA START =====-->

    <div class="contact2 sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="heading2">
                        <div class="contact2-form">
                            <span class="sub-title" data-aos="zoom-in-left" data-aos-duration="900"><img
                                    src="assets/img/icons/span3.svg" alt="">CONTACT US </span>
                            <h2 class="text-anime-style-3">Lets Work Together</h2>
                            <p class="mt-16" data-aos="fade-right" data-aos-duration="900">eady to take your social
                                media presence to the next level? Let’s work together to create impactful strategies drive
                                engagement, growth, and success for your brand.</p>
                            <form action="#" data-aos="fade-right" data-aos-duration="1000">
                                <div class="row mt-16">
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="text" placeholder="First Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="text" placeholder="Last Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="email" placeholder="Email Address">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single-input">
                                            <input type="number" placeholder="Phone Number">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="single-input">
                                            <select class="wide">
                                                <option value="1">Service Type</option>
                                                <option value="2">Option 1</option>
                                                <option value="3">Option 2</option>
                                                <option value="4">Option 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="single-input">
                                            <textarea rows="5" placeholder="How can we help you?"></textarea>
                                        </div>
                                        <div class="button mt-30">
                                            <button class="theme-btn3" type="submit">Send <span class="arrow1"><i
                                                        class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i
                                                        class="fa-solid fa-arrow-right"></i></span></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact2-image image-anime reveal ml-40 md:ml-0 sm:ml-0 md:mt-30 sm:mt-30">
                        <img class="w-full" src="assets/img/others/contact2-image.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===== CONTACT AREA END =====-->
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/services/show.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/pages/services/show.js') }}"></script>
@endpush
