<script src="{{ asset('admin/assets/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/fslightbox.js') }}"></script>
<script src="{{ asset('admin/assets/js/simplebar.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/prism.js') }}"></script>
<script src="{{ asset('admin/assets/js/clipboard.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/fullcalendar.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/jsvectormap.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/world-merc.js') }}"></script>
<script src="{{ asset('admin/assets/js/quill.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/custom.js') }}"></script>

{{-- Görsel alanı davranışı: olay delegasyonu ile çalışır, sayfa JS'i gerektirmez. --}}
<script type="module" src="{{ asset('admin/assets/js/core/media-field.js') }}"></script>

@stack('admin.scripts')
