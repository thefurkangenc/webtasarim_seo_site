@extends('layout.app')
@section('title', 'Blog')
@section('content')
    <!--===== HERO AREA START =====-->

    <div class="inner-hero" style="background-image: url({{ asset('assets/img/bg/hero12-bg1.png') }});">
     <div class="container">
         <div class="row">
             <div class="col-lg-8 m-auto text-center">
                 <div class="inner-main-heading">
                     <h1>Blog</h1>
                     <div class="breadcrumbs-pages">
                         <ul>
                             <li><a href="{{ route('anasayfa') }}">Ana Sayfa</a></li>
                             <li class="angle"><i class="fa-solid fa-angle-right"></i></li>
                             <li>Blog</li>
                         </ul>
                     </div>
                 </div>
             </div>
         </div>
     </div>
    </div>

    <!--===== HERO AREA START =====-->

    <!--===== BLOG AREA START =====-->

    <div class="blog1 sp">
     <div class="container">
        <div class="vl-blog-12-item mt-30" data-aos="fade-up" data-aos-duration="1100">
           <div class="row">
              <div class="col-lg-6">
                 <div class=" vl-blog-12-thumb image-anime overflow-hidden _relative">
                    <img class="w-full" src="assets/img/blog/blog-page1-image1.png" alt="">
                 </div>
              </div>
              <div class="col-lg-6">
                 <div class="vl-blog-12-content heading2">
                    <div class="vl-blog12-meta pb-16">
                       <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                       <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> John Doe</a>
                    </div>
                    <h4><a href="blog-details.html">Transform Your Online Presence with Expert SEO Strategies – Drive Traffic, Conversions & Business Growth with SEOX.</a></h4>
                    <p class="mt-16">the digital age, simply having a website isn't enough. To stand out in a sea of competition, you need strategic search engine optimization (SEO) that positions your brand.</p>
                    <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
                 </div>
              </div>
           </div>
        </div>

        <div class="vl-blog-12-item mt-30" data-aos="fade-up" data-aos-duration="900">
           <div class="row">
              <div class="col-lg-6">
                 <div class="vl-blog-12-content heading2">
                    <div class="vl-blog12-meta pb-16">
                       <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                       <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> John Doe</a>
                    </div>
                    <h4><a href="blog-details.html">Maximize Your Business Success with Tailored SEO Solutions – Partner with SEOX to Optimize Your Digital Growth.</a></h4>
                    <p class="mt-16">Are you ready to unlock the full potential of your business online? With SEOX, achieving top rankings on search engines has never been easier, We combine innovative SEO techniques.</p>
                    <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
                 </div>
              </div>
              <div class="col-lg-6">
                 <div class=" vl-blog-12-thumb image-anime overflow-hidden _relative">
                    <img class="w-full" src="assets/img/blog/blog-page1-image2.png" alt="">
                 </div>
              </div>
           </div>
        </div>

        <div class="vl-blog-12-item mt-30" data-aos="fade-up" data-aos-duration="900">
         <div class="row">
             <div class="col-lg-6">
                 <div class=" vl-blog-12-thumb image-anime overflow-hidden _relative">
                    <img class="w-full" src="assets/img/blog/blog-page1-image3.png" alt="">
                 </div>
              </div>
            <div class="col-lg-6">
               <div class="vl-blog-12-content heading2">
                  <div class="vl-blog12-meta pb-16">
                     <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                     <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> John Doe</a>
                  </div>
                  <h4><a href="blog-details.html">"Mastering the Art of Search Engine Optimization: A Comprehensive Guide to Driving Organic Traffic and Building Online Authority"</a></h4>
                  <p class="mt-16">Search Engine Optimization (SEO) is the backbone of modern digital marketing, offering businesses a way to enhance their visibility and attract targeted traffic. </p>
                  <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
               </div>
            </div>
         </div>
      </div>

      <div class="vl-blog-12-item mt-30" data-aos="fade-up" data-aos-duration="900">
         <div class="row">
            <div class="col-lg-6">
               <div class="vl-blog-12-content heading2">
                  <div class="vl-blog12-meta pb-16">
                     <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                     <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> John Doe</a>
                  </div>
                  <h4><a href="blog-details.html">"Creating Impactful Content Marketing Strategies: How to Boost Engagement and Strengthen Your Digital Presence in Competitive Markets"</a></h4>
                  <p class="mt-16">In today’s crowded digital landscape, creating compelling and relevant content is crucial for standing out. This blog explores how to craft impactful content marketing strategies. </p>
                  <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
               </div>
            </div>
            <div class="col-lg-6">
               <div class=" vl-blog-12-thumb image-anime overflow-hidden _relative">
                  <img class="w-full" src="assets/img/blog/blog-page1-image4.png" alt="">
               </div>
            </div>
         </div>
      </div>

      <div class="vl-blog-12-item mt-30" data-aos="fade-up" data-aos-duration="900">
         <div class="row">
             <div class="col-lg-6">
                 <div class=" vl-blog-12-thumb image-anime overflow-hidden _relative">
                    <img class="w-full" src="assets/img/blog/blog-page1-image5.png" alt="">
                 </div>
              </div>
            <div class="col-lg-6">
               <div class="vl-blog-12-content heading2">
                  <div class="vl-blog12-meta pb-16">
                     <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                     <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> John Doe</a>
                  </div>
                  <h4><a href="blog-details.html">"Unleashing the Full Potential of Social Media Marketing: Proven Tactics to Elevate Your Brand's Voice and Customer Connection"</a></h4>
                  <p class="mt-16">Social media marketing is more than just posting updates—it’s about building relationships, telling your brand’s story, and engaging with your audience on a deeper level.  </p>
                  <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
               </div>
            </div>
         </div>
      </div>

      <div class="vl-blog-12-item mt-30" data-aos="fade-up" data-aos-duration="900">
         <div class="row">
            <div class="col-lg-6">
               <div class="vl-blog-12-content heading2">
                  <div class="vl-blog12-meta pb-16">
                     <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                     <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> John Doe</a>
                  </div>
                  <h4><a href="blog-details.html">"Maximizing ROI with PPC Advertising: A Step-by-Step Approach to Combining Paid Ads with SEO for Better Results"</a></h4>
                  <p class="mt-16">PPC advertising is a powerful tool for immediate results, but its true strength lies in strategic integration with SEO. This blog provides a step-by-step guide to blending paid advertising. </p>
                  <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
               </div>
            </div>
            <div class="col-lg-6">
               <div class=" vl-blog-12-thumb image-anime overflow-hidden _relative">
                  <img class="w-full" src="assets/img/blog/blog-page1-image6.png" alt="">
               </div>
            </div>
         </div>
      </div>

      <div class="space60"></div>
         <div class="row">
             <div class="col-12 m-auto">
                <div class="theme-pagination text-center">
                 <ul>
                     <li><a href="#"><i class="fa-solid fa-angle-left"></i></a></li>
                     <li><a class="active" href="#">01</a></li>
                     <li><a href="#">02</a></li>
                     <li>...</li>
                     <li><a href="#">12</a></li>
                     <li><a href="#"><i class="fa-solid fa-angle-right"></i></a></li>
                 </ul>
                </div>
             </div>
         </div>

     </div>
  </div>

  <!--===== BLOG AREA END =====-->


