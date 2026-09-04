<!DOCTYPE html>
<html lang="en">

<head>
    @include('layout.partials.meta')
    @include('layout.partials.tracking', ['placement' => 'head'])

    @include('layout.partials.css')
</head>

<body class="body1">
    @include('layout.partials.tracking', ['placement' => 'body'])
    <div class="paginacontainer">

        <div class="progress-wrap">
            <svg class="progress-circle svg-content" width="100%" height="100%" viewbox="-1 -1 102 102">
                <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"></path>
            </svg>
        </div>

    </div>

    <!--=====progress END=======-->

    <!--=====PRELOADER START=======-->
    <div class="preloader9">
        <!-- Preloader -->
        <div id="preloader">
            <!-- Progress Bar at Top -->
            <div class="progress-bar"></div>
            <!-- Title Logo in Center with Rotation -->
            <div class="title-logo">
                <img src="assets/img/logo/preloader-icon1.png" alt="SEO Marketing Logo">
            </div>
        </div>
    </div>


    <!--=====PRELOADER END=======-->

    <!--=====HEADER START=======-->
    @include('layout.partials.header')

    <!--=====HEADER END =======-->

    <main>

        @yield('content')


    </main>


    @include('layout.partials.footer')

    @include('layout.partials.cookie-banner')

    <!--=== js === -->
    @include('layout.partials.scripts')
    @include('layout.partials.integrations')

</body>

</html>
