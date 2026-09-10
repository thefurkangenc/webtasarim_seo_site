@props([
    // Log modül anahtarı — config/activity-log.php'deki 'modules' anahtarı.
    'module',
    // Modal başlığı; verilmezse modülün etiketinden türetilir.
    'title' => null,
    'label' => 'Log Kayıtları',
])

{{--
    Modül index sayfalarındaki "Log Kayıtları" butonu. Tıklanınca o modülün
    denetim kayıtları bir modalda açılır; modaldan merkezi sayfaya geçilebilir.

    Kullanımı tek satır — her modülde tekrar kod yazılmaz:

        <x-admin::activity-log-button module="blog" />

    Davranışı core/activity-log.js verir; script bu bileşen sayfada en az bir
    kez göründüğünde kendiliğinden yüklenir (@once), sayfa JS'inin bir şey
    çağırmasına gerek yoktur.
--}}

@can('activity-log.index')
    @once
        @push('admin.scripts')
            <script type="module" src="{{ asset('admin/assets/js/core/activity-log.js') }}"></script>
        @endpush
    @endonce

    <button type="button" data-activity-log="{{ $module }}"
        data-activity-title="{{ $title ?? (config("activity-log.modules.{$module}.label") ?? 'Log Kayıtları').' — Log Kayıtları' }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]']) }}>
        <i class="material-symbols-outlined !text-[19px]">history</i>
        {{ $label }}
    </button>
@endcan
