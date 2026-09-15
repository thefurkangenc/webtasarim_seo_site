<script src="{{ asset('assets/admin/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/fslightbox.js') }}"></script>
<script src="{{ asset('assets/admin/js/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/prism.js') }}"></script>
<script src="{{ asset('assets/admin/js/clipboard.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/fullcalendar.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/jsvectormap.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/world-merc.js') }}"></script>
<script src="{{ asset('assets/admin/js/quill.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/custom.js') }}"></script>

{{-- Flatpickr UMD dosyaları: ESM export'u yok, klasik <script> ile window.flatpickr
     global'ini kurar. core/datepicker.js bu global'i kullanır. tr.js, flatpickr.js'ten
     sonra yüklenmeli — window.flatpickr.l10ns içine ekleme yapıyor. --}}
<script src="{{ asset('assets/admin/js/vendor/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/admin/js/vendor/flatpickr/tr.js') }}"></script>

{{-- Görsel alanı, select ve tarih alanı davranışı: olay delegasyonu ile çalışır,
     sayfa JS'i gerektirmez. --}}
<script type="module" src="{{ asset('assets/admin/js/core/media-field.js') }}"></script>
<script type="module" src="{{ asset('assets/admin/js/core/media-gallery.js') }}"></script>
<script type="module" src="{{ asset('assets/admin/js/core/select.js') }}"></script>
<script type="module" src="{{ asset('assets/admin/js/core/datepicker.js') }}"></script>
<script type="module" src="{{ asset('assets/admin/js/core/map-picker.js') }}"></script>

{{-- tag-input.js BURADA yükleniyor çünkü iki bileşen paylaşıyor: form.tags ve
     form.chips. Bileşenin içinde @once ile eklenirse Blade her çağrı yeri için
     ayrı bir kimlik üretir ve aynı <script> etiketi iki kez basılır. Tek
     kullanıcısı olan modüller (repeater, video-field) kendi bileşenlerinde
     kalmaya devam ediyor. --}}
<script type="module" src="{{ asset('assets/admin/js/core/tag-input.js') }}"></script>
<script type="module" src="{{ asset('assets/admin/js/core/phone-field.js') }}"></script>

{{-- Form label'larındaki (?) yardım ikonu — olay delegasyonu, sayfa JS'i gerektirmez. --}}
<script type="module" src="{{ asset('assets/admin/js/core/help-popover.js') }}"></script>
<script type="module" src="{{ asset('assets/admin/js/core/sidebar-scroll.js') }}"></script>

{{-- Header: global arama (Ctrl+K) ve bildirim merkezi. İkisi de her sayfada
     var, sayfa JS'i gerektirmez. --}}
<script type="module" src="{{ asset('assets/admin/js/core/global-search.js') }}"></script>
<script type="module" src="{{ asset('assets/admin/js/core/notifications.js') }}"></script>

@stack('admin.scripts')
