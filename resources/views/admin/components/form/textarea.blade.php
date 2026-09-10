@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => null,
    'required' => false,
    'help' => null,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $field = \App\Support\Field::name($name);
    $id = \App\Support\Field::id($name);
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$id" :required="$required" :help="$help">{{ $label }}</x-admin::form.label>
    @endif

    <textarea
        name="{{ $field }}"
        id="{{ $id }}"
        @if ($rows) rows="{{ $rows }}" @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'h-[120px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[12px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500']) }}>{{ old($name, $value) }}</textarea>

    <x-admin::form.error :name="$name" />
</div>
