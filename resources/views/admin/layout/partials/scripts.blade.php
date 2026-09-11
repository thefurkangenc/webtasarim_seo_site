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

{{-- Flatpickr UMD dosyaları: ESM export'u yok, klasik <script> ile window.flatpickr
     global'ini kurar. core/datepicker.js bu global'i kullanır. tr.js, flatpickr.js'ten
     sonra yüklenmeli — window.flatpickr.l10ns içine ekleme yapıyor. --}}
<script src="{{ asset('admin/assets/js/vendor/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('admin/assets/js/vendor/flatpickr/tr.js') }}"></script>

{{-- Görsel alanı, select ve tarih alanı davranışı: olay delegasyonu ile çalışır,
     sayfa JS'i gerektirmez. --}}
<script type="module" src="{{ asset('admin/assets/js/core/media-field.js') }}"></script>
<script type="module" src="{{ asset('admin/assets/js/core/media-gallery.js') }}"></script>
<script type="module" src="{{ asset('admin/assets/js/core/select.js') }}"></script>
<script type="module" src="{{ asset('admin/assets/js/core/datepicker.js') }}"></script>
<script type="module" src="{{ asset('admin/assets/js/core/map-picker.js') }}"></script>

{{-- Form label'larındaki (?) yardım ikonu — olay delegasyonu, sayfa JS'i gerektirmez. --}}
<script type="module" src="{{ asset('admin/assets/js/core/help-popover.js') }}"></script>
<script type="module" src="{{ asset('admin/assets/js/core/sidebar-scroll.js') }}"></script>

@stack('admin.scripts')
