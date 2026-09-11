@props([
    // Kaydın model sınıfı ve kimliği; kayıt yoksa (yeni form) bileşen basılmaz.
    'model' => null,
    'label' => 'Revizyonlar',
])

{{--
    Düzenleme formlarındaki "Revizyonlar" butonu. Tıklanınca o kaydın eski
    sürümleri bir modalda listelenir, oradan karşılaştırma ve geri yükleme
    yapılır.

        <x-admin::revision-button :model="$blog" />

    Davranışı core/revisions.js verir; script bileşen sayfada en az bir kez
    göründüğünde kendiliğinden yüklenir (@once).
--}}

@if ($model?->exists)
    @can('revision.index')
        @once
            @push('admin.scripts')
                <script type="module" src="{{ asset('admin/assets/js/core/revisions.js') }}"></script>
            @endpush
        @endonce

        <button type="button" data-revision-history="{{ $model::class }}" data-revision-id="{{ $model->getKey() }}"
            data-revision-title="Revizyon Geçmişi — {{ $model->revisionLabel() }}"
            {{ $attributes->merge(['class' => 'inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]']) }}>
            <i class="material-symbols-outlined !text-[19px]">settings_backup_restore</i>
            {{ $label }}
        </button>
    @endcan
@endif
