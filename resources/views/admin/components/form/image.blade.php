@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => 'JPG, PNG veya WEBP. En fazla 4 MB.',
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$name" :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    @if ($value)
        <img src="{{ app(\App\Services\Media\MediaService::class)->url($value) }}" alt="{{ $label }}"
            class="w-[120px] h-[80px] object-cover rounded-md border border-gray-200 dark:border-[#172036] mb-[10px]">
    @endif

    <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] p-[12px] block w-full outline-0 transition-all focus:border-primary-500 file:ltr:mr-[12px] file:rtl:ml-[12px] file:py-[6px] file:px-[15px] file:rounded-md file:border-0 file:bg-primary-500 file:text-white file:cursor-pointer']) }}>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
</div>
