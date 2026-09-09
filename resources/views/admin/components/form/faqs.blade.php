@props([
    // HasFaqs kullanan model; yeni kayıtta null olabilir.
    'model' => null,
    'name' => 'faqs',
    'label' => 'Sıkça Sorulan Sorular',
    'hint' => 'Mevcut sorulardan seçin. Aynı soru birden fazla içeriğe bağlanabilir.',
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $field = \App\Support\Field::name($name);

    // Doğrulama hatası sonrası formda kalınırsa old() yalnızca id listesi
    // taşır — soru metnini yeniden okumak gerekir. whereIn() kullanıcının
    // seçim sırasını korumaz (MySQL genelde birincil anahtar sırasına
    // döner); syncFaqs() sıraya göre sort_order yazdığı için sıra burada
    // elle $oldIds'e göre yeniden kurulur. Aksi halde model ilişkisinden
    // (yeni kayıtta boş) okunur.
    $oldIds = old($name);

    if ($oldIds) {
        $byId = \App\Models\Faq\Faq::query()->whereIn('id', $oldIds)->get(['id', 'question'])->keyBy('id');
        $selected = collect($oldIds)->map(fn ($id) => $byId->get($id))->filter()->values();
    } else {
        // faqs() ilişkisi faqables ile join yapıyor — o tabloda da bir 'id'
        // kolonu var, tablo adı verilmeden 'id' seçmek "ambiguous column" hatası verir.
        $selected = $model?->exists ? $model->faqs()->get(['faqs.id', 'faqs.question']) : collect();
    }
@endphp

@once
    @push('admin.scripts')
        <script type="module" src="{{ asset('admin/assets/js/core/faq-picker.js') }}"></script>
    @endpush
@endonce

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label>{{ $label }}</x-admin::form.label>
    @endif

    <div data-faq-field data-faq-name="{{ $field }}" data-faq-endpoint="{{ route('admin.faq.datatable') }}"
        class="rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[10px] flex flex-wrap items-center gap-[8px] text-sm">

        <div data-faq-chips class="contents">
            @foreach ($selected as $faq)
                <span data-faq-chip data-faq-id="{{ $faq->id }}"
                    class="inline-flex items-center gap-[6px] py-[6px] px-[12px] rounded-md text-xs bg-primary-50 dark:bg-[#15203c] text-primary-500 border border-primary-100 dark:border-[#172036] max-w-[280px]">
                    <input type="hidden" name="{{ $field }}[]" value="{{ $faq->id }}">
                    <i class="material-symbols-outlined !text-[15px] shrink-0">help</i>
                    <span class="truncate" title="{{ $faq->question }}">{{ $faq->question }}</span>
                    <button type="button" data-faq-remove
                        class="shrink-0 leading-none transition-all hover:text-danger-500">
                        <i class="ri-close-line"></i>
                    </button>
                </span>
            @endforeach
        </div>

        <button type="button" data-faq-open
            class="inline-flex items-center gap-[5px] py-[6px] px-[12px] rounded-md text-xs text-gray-500 dark:text-gray-400 border border-dashed border-gray-200 dark:border-[#172036] transition-all hover:text-primary-500 hover:border-primary-300 dark:hover:border-primary-500/50">
            <i class="material-symbols-outlined !text-[15px]">add</i>
            Soru Seç
        </button>
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
