@props([
    'name',
    'label' => null,
    'checked' => false,
    'hint' => null,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $field = \App\Support\Field::name($name);
    $id = \App\Support\Field::id($name);
@endphp

<div class="{{ $wrapper }}">
    {{-- Kapalıyken de bir değer gitsin diye gizli 0; checkbox işaretliyse 1 onu ezer. --}}
    <input type="hidden" name="{{ $field }}" value="0">

    <label class="flex items-center gap-[10px] cursor-pointer select-none w-fit">
        <span class="relative inline-block">
            <input type="checkbox" name="{{ $field }}" id="{{ $id }}" value="1"
                @checked((bool) old($name, $checked))
                {{ $attributes->merge(['class' => 'peer sr-only']) }}>
            <span
                class="block w-[44px] h-[24px] rounded-full bg-gray-200 dark:bg-[#172036] transition-all peer-checked:bg-primary-500"></span>
            <span
                class="absolute top-[3px] ltr:left-[3px] rtl:right-[3px] w-[18px] h-[18px] rounded-full bg-white transition-all peer-checked:ltr:translate-x-[20px] peer-checked:rtl:-translate-x-[20px]"></span>
        </span>

        @if ($label)
            <span class="text-black dark:text-white font-medium">{{ $label }}</span>
        @endif
    </label>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
