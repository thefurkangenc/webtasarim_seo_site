{{-- Menü öğesi rozeti: MenuService, config/admin-menu.php'deki badge sınıfından üretir. --}}
@if (! empty($badge))
    <span
        class="ltr:ml-auto rtl:mr-auto inline-flex items-center justify-center min-w-[20px] h-[20px] px-[6px] rounded-full text-[11px] font-medium text-white {{ $badge['status'] === 'critical' ? 'bg-danger-500' : 'bg-warning-500' }}">
        {{ $badge['count'] }}
    </span>
@endif
