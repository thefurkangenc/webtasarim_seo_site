<!DOCTYPE html>
<html dir="ltr">

<head>
    @include('admin.layout.partials.meta')

    <!-- Links Of CSS File -->
    @include('admin.layout.partials.styles')
</head>

<body>

    <!-- Sidebar -->
    @include('admin.layout.partials.sidebar')
    <!-- End Sidebar -->

    <!-- Header -->
    @include('admin.layout.partials.header')
    <!-- End Header -->

    <!-- Main Content -->
    <div class="main-content transition-all flex flex-col overflow-hidden min-h-screen" id="main-content">

        @yield('content')

        <div class="grow"></div>

        <!-- Footer -->
        @include('admin.layout.partials.footer')

    </div>
    <!-- End Main Content -->

    <!-- Links Of JS File -->
    @include('admin.layout.partials.scripts')
</body>

</html>
