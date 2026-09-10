@props([
    'name' => null,
    'label' => null,
    'checked' => false,
    'hint' => null,
    // config/form-help.php anahtarı — verilirse etiketin yanına (?) yardım ikonu.
    'help' => null,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
    // `bare`: forma bağlı değil, isim/hidden-input/hata yuvası göndermez —
    // AJAX ile anlık açılıp kapanan salt görsel anahtarlar için (örn.
    // entegrasyon kartları). $attributes ile gelen data-* öznitelikler
    // checkbox'a geçer.
    'bare' => false,
])

@php
    $field = $name ? \App\Support\Field::name($name) : null;
    $id = $name ? \App\Support\Field::id($name) : null;
@endphp

<div class="{{ $wrapper }}">
    @unless ($bare)
        {{-- Kapalıyken de bir değer gitsin diye gizli 0; checkbox işaretliyse 1 onu ezer. --}}
        <input type="hidden" name="{{ $field }}" value="0">
    @endunless

    <div class="flex items-center w-fit">
    <label class="flex items-center gap-[10px] cursor-pointer select-none w-fit">
        <span class="relative inline-block">
            <input type="checkbox" @unless ($bare) name="{{ $field }}" id="{{ $id }}" @endunless value="1"
                @checked((bool) ($bare ? $checked : old($name, $checked)))
                {{ $attributes->merge(['class' => 'peer sr-only']) }}>
            <span
                class="block w-[44px] h-[24px] rounded-full bg-gray-200 dark:bg-[#172036] transition-all peer-checked:bg-primary-500 peer-focus-visible:ring-[3px] peer-focus-visible:ring-primary-500/25"></span>
            <span
                class="absolute top-[3px] ltr:left-[3px] rtl:right-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-[0_1px_3px_rgba(16,24,40,.25)] transition-all peer-checked:ltr:translate-x-[20px] peer-checked:rtl:-translate-x-[20px]"></span>
        </span>

        @if ($label)
            <span class="text-black dark:text-white font-medium">{{ $label }}</span>
        @endif
    </label>
        @if ($help)<x-admin::form.help :topic="$help" />@endif
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    @unless ($bare)
        <x-admin::form.error :name="$name" />
    @endunless
</div>
