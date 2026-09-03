@props([
    'name',
    'label' => null,
    'value' => null,
    'time' => true,
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $field = \App\Support\Field::name($name);
    $id = \App\Support\Field::id($name);
    // Flatpickr'ın forma gönderdiği ham değer; Y-m-d [H:i] biçiminde olmalı,
    // Laravel'in `date` kuralı bunu doğrudan anlar.
    $raw = old($name, $value instanceof \Illuminate\Support\Carbon ? $value->format($time ? 'Y-m-d H:i' : 'Y-m-d') : $value);
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$id" :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    <input type="text" name="{{ $field }}" id="{{ $id }}" value="{{ $raw }}"
        data-datepicker data-datepicker-time="{{ $time ? '1' : '0' }}"
        autocomplete="off" placeholder="{{ $time ? 'gg.aa.yyyy ss:dd' : 'gg.aa.yyyy' }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500']) }}>

    <x-admin::form.error :name="$name" />
</div>
