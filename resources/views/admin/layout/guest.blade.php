<!DOCTYPE html>
<html dir="ltr">

<head>
    @include('admin.layout.partials.meta')

    <!-- Links Of CSS File -->
    @include('admin.layout.partials.styles')
</head>

<body>

    <!-- Light/Dark Mode Button -->
    <button type="button"
        class="light-dark-toggle leading-none inline-block transition-all text-[#fe7a36] absolute top-[20px] md:top-[25px] ltr:right-[20px] rtl:left-[20px] ltr:md:right-[25px] rtl:md:left-[25px]"
        id="light-dark-toggle">
        <i class="material-symbols-outlined !text-[20px] md:!text-[22px]">
            light_mode
        </i>
    </button>

    @yield('content')

    <!-- Links Of JS File -->
    @include('admin.layout.partials.scripts')
</body>

</html>
