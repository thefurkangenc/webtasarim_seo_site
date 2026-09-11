@props([
    'name',
    'label' => null,
    /*
    | Kolon tanımları. Her biri:
    |   key         -> alan adı ('label'), form adı `name[i][key]` olur
    |   label       -> başlık satırında görünen metin
    |   type        -> 'text' (varsayılan) | 'select'
    |   options     -> select için değer => etiket
    |   placeholder -> metin alanı için
    |   width       -> Tailwind genişlik sınıfı ('flex-1', 'w-[130px]')
    */
    'columns' => [],
    // list<array<string, mixed>> — mevcut satırlar.
    'rows' => [],
    'addLabel' => 'Satır ekle',
    'emptyText' => 'Henüz satır yok.',
    'max' => 12,
    'hint' => null,
    'help' => null,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $field = \App\Support\Field::name($name);
    $rows = collect(old($name, $rows ?? []))->filter(fn ($row) => is_array($row))->values();

    $inputClass = 'h-[38px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[12px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500';
    $selectClass = $inputClass.' cursor-pointer';
@endphp

@once
    @push('admin.scripts')
        <script type="module" src="{{ asset('admin/assets/js/core/repeater.js') }}"></script>
    @endpush
@endonce

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :help="$help">{{ $label }}</x-admin::form.label>
    @endif

    <div data-repeater data-repeater-max="{{ $max }}">
        {{-- Başlık satırı: mobilde her satır kendi etiketini taşıdığı için gizli. --}}
        <div class="hidden sm:flex items-center gap-[8px] mb-[6px] ltr:pl-[26px] rtl:pr-[26px] ltr:pr-[36px] rtl:pl-[36px]">
            @foreach ($columns as $column)
                <span class="{{ $column['width'] ?? 'flex-1' }} text-[11px] font-medium uppercase tracking-[.4px] text-gray-400">
                    {{ $column['label'] }}
                </span>
            @endforeach
        </div>

        <div data-repeater-list class="flex flex-col gap-[8px]">
            @foreach ($rows as $index => $row)
                @include('admin.components.form.partials.repeater-row', [
                    'field' => $field,
                    'columns' => $columns,
                    'rowIndex' => $index,
                    'row' => $row,
                    'inputClass' => $inputClass,
                    'selectClass' => $selectClass,
                ])
            @endforeach
        </div>

        <p data-repeater-empty
            class="{{ $rows->isEmpty() ? '' : 'hidden' }} !mb-0 py-[14px] text-center text-xs text-gray-500 dark:text-gray-400 rounded-md border border-dashed border-gray-200 dark:border-[#172036]">
            {{ $emptyText }}
        </p>

        {{-- Yeni satırın iskeleti. __index__ JS tarafından benzersiz bir sayıyla
             değiştirilir. <template> içeriği forma gönderilmez. --}}
        <template data-repeater-template>
            @include('admin.components.form.partials.repeater-row', [
                'field' => $field,
                'columns' => $columns,
                'rowIndex' => '__index__',
                'row' => [],
                'inputClass' => $inputClass,
                'selectClass' => $selectClass,
            ])
        </template>

        <button type="button" data-repeater-add
            class="mt-[10px] inline-flex items-center gap-[6px] py-[8px] px-[14px] text-xs text-black dark:text-white transition-all rounded-md border border-dashed border-gray-300 dark:border-[#172036] hover:border-primary-500 hover:text-primary-500">
            <i class="material-symbols-outlined !text-[16px]">add</i> {{ $addLabel }}
        </button>
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
