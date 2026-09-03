@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => null,
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$name" :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($rows) rows="{{ $rows }}" @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'h-[140px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500']) }}>{{ old($name, $value) }}</textarea>

    <x-admin::form.error :name="$name" />
</div>
