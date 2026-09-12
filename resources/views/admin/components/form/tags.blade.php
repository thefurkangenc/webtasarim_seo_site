@props([
    // HasTags kullanan model; yeni kayıtta null olabilir.
    'model' => null,
    'name' => 'tags',
    'label' => 'Etiketler',
    'hint' => 'Enter ya da virgül ile ekleyin. Olmayan etiket otomatik oluşturulur.',
    'help' => 'common.tags',
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $tags = old($name, $model?->exists ? $model->tagNames() : []);
    $field = \App\Support\Field::name($name);
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :help="$help">{{ $label }}</x-admin::form.label>
    @endif

    <div data-tag-input data-tag-name="{{ $field }}" data-tag-endpoint="{{ route('admin.tags.search') }}"
        class="relative rounded-md border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[8px] min-h-[42px] flex flex-wrap items-center gap-[6px] cursor-text transition-all focus-within:border-primary-500 text-sm">

        <div data-tag-chips class="contents">
            @foreach ($tags as $tag)
                <span data-tag-chip
                    class="inline-flex items-center gap-[5px] py-[5px] px-[10px] rounded-md text-xs bg-primary-50 dark:bg-[#15203c] text-primary-500 border border-primary-100 dark:border-[#172036]">
                    <input type="hidden" name="{{ $field }}[]" value="{{ $tag }}">
                    <span>{{ $tag }}</span>
                    <button type="button" data-tag-remove class="leading-none transition-all hover:text-danger-500">
                        <i class="ri-close-line"></i>
                    </button>
                </span>
            @endforeach
        </div>

        <input type="text" data-tag-field autocomplete="off" placeholder="Etiket yazın..."
            class="grow min-w-[140px] bg-transparent border-0 outline-0 text-black dark:text-white py-[5px] placeholder:text-gray-500 dark:placeholder:text-gray-400">

        <ul data-tag-suggestions
            class="hidden absolute z-[3] top-full ltr:left-0 rtl:right-0 mt-[4px] w-full max-h-[220px] overflow-y-auto rounded-md border border-gray-100 dark:border-[#172036] bg-white dark:bg-[#0c1427] shadow-3xl py-[5px]"></ul>
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
