@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $field = \App\Support\Field::name($name);
    $id = \App\Support\Field::id($name);
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$id" :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $field }}"
        id="{{ $id }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'h-[55px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500']) }}>

    <x-admin::form.error :name="$name" />
</div>
