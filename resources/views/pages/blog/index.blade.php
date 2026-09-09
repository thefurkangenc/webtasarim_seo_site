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

    <div class="blog2 sp">
     <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="vl-blog-11-item mt-30 aos-init aos-animate" data-aos="fade-up" data-aos-duration="900">
                    <div class=" vl-blog-11-thumb image-anime overflow-hidden _relative">
                        <img class="w-full" src="assets/img/blog/blog-page1-image1.png" alt="">
                     </div>
                    <div class="vl-blog-11-content heading2">
                        <div class="vl-blog11-meta pb-16">
                            <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                            <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> Dustin Turcotte</a>
                        </div>
                        <h4><a href="blog-details.html">Discover the emerging trends that are reshaping the startup ecosystem.</a></h4>
                        <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
                    </div>
                 </div>
            </div>

            <div class="col-lg-4">
                <div class="vl-blog-11-item mt-30 aos-init aos-animate" data-aos="fade-up" data-aos-duration="900">
                    <div class=" vl-blog-11-thumb image-anime overflow-hidden _relative">
                        <img class="w-full" src="assets/img/blog/blog-page1-image2.png" alt="">
                     </div>
                    <div class="vl-blog-11-content heading2">
                        <div class="vl-blog11-meta pb-20">
                            <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                            <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> Alex Carey</a>
                        </div>
                        <h4><a href="blog-details.html">Learn the secrets to creating a brand that resonates with your audience.</a></h4>
                        <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
                    </div>
                 </div>
            </div>

            <div class="col-lg-4">
                <div class="vl-blog-11-item mt-30 aos-init aos-animate" data-aos="fade-up" data-aos-duration="900">
                    <div class=" vl-blog-11-thumb image-anime overflow-hidden _relative">
                        <img class="w-full" src="assets/img/blog/blog-page1-image3.png" alt="">
                     </div>
                    <div class="vl-blog-11-content heading2">
                        <div class="vl-blog11-meta pb-20">
                            <a href="#" class="date"><img src="assets/img/icons/date1.svg" alt=""> 12/12/2024</a>
                            <a href="#" class="author"><img src="assets/img/icons/author1.svg" alt=""> Patricia Sanders</a>
                        </div>
                        <h4><a href="blog-details.html">Mastering SEO: The Ultimate Guide to Boosting Website Traffic</a></h4>
                        <a href="blog-details.html" class="learn">Read More <span class="arrow1"><i class="fa-solid fa-arrow-right"></i></span><span class="arrow2"><i class="fa-solid fa-arrow-right"></i></span></a>
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


