@props([
    'name' => 'phone',
    'countryName' => 'country_id',
    'label' => 'Telefon',
    'phone' => null,
    'countryId' => null,
    'countries' => null,
    'required' => false,
    'help' => null,
    'hint' => null,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $countries = collect($countries ?? []);
    $selectedId = old($countryName, $countryId) ?: $countries->firstWhere('iso2', 'TR')?->id ?? $countries->first()?->id;
    $selected = $countries->firstWhere('id', (int) $selectedId) ?? $countries->first();
    $digits = old($name, $phone);
    $display = $selected && $digits
        ? \App\Support\Phone::format($digits, $selected->mask)
        : '';
    $phoneField = \App\Support\Field::name($name);
    $countryField = \App\Support\Field::name($countryName);
    $phoneId = \App\Support\Field::id($name);
    $placeholder = $selected ? str_replace('0', '5', $selected->mask) : '';
@endphp

<div class="{{ $wrapper }}">
    @if ($label)
        <x-admin::form.label :for="$phoneId" :required="$required" :help="$help">{{ $label }}</x-admin::form.label>
    @endif

    <div data-phone-field data-phone-countries="{{ $countries->map->toPayload()->values()->toJson() }}" class="relative">
        <input type="hidden" name="{{ $countryField }}" value="{{ $selected?->id }}" data-phone-country>
        <input type="hidden" name="{{ $phoneField }}" value="{{ $digits }}" data-phone-value>

        <div class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] flex transition-all focus-within:border-primary-500">
            <button type="button" data-phone-toggle aria-expanded="false"
                class="inline-flex items-center gap-[6px] h-full px-[12px] shrink-0 border-r border-gray-200 dark:border-[#172036] transition-all hover:bg-gray-50 dark:hover:bg-[#15203c]">
                <span data-phone-flag class="leading-none text-lg">{{ $selected?->flag }}</span>
                <span data-phone-dial class="font-medium whitespace-nowrap">{{ $selected ? '+'.$selected->dial_code : '' }}</span>
                <span data-phone-iso class="text-xs text-gray-500 dark:text-gray-400">{{ $selected?->iso2 }}</span>
                <i class="ri-arrow-down-s-line text-lg text-gray-500 dark:text-gray-400"></i>
            </button>

            <input type="tel" id="{{ $phoneId }}" data-phone-display value="{{ $display }}"
                inputmode="numeric" autocomplete="tel-national" placeholder="{{ $placeholder }}"
                @if ($required) required @endif
                class="h-full min-w-0 flex-1 bg-transparent px-[14px] outline-0 placeholder:text-gray-500 dark:placeholder:text-gray-400">
        </div>

        <ul data-phone-menu hidden
            class="bg-white shadow-3xl rounded-md mt-[6px] py-[8px] absolute ltr:left-0 rtl:right-0 w-[240px] z-[5] dark:bg-[#0c1427] dark:shadow-none">
            @foreach ($countries as $country)
                <li>
                    <button type="button" data-phone-option="{{ $country->id }}"
                        class="flex items-center gap-[10px] w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-[#15203c]">
                        <span class="leading-none text-lg">{{ $country->flag }}</span>
                        <span class="font-medium">+{{ $country->dial_code }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $country->iso2 }}</span>
                        <span class="min-w-0 truncate text-sm">{{ $country->name }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    @if ($hint)
        <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">{{ $hint }}</span>
    @endif

    <x-admin::form.error :name="$name" />
    <x-admin::form.error :name="$countryName" />
</div>
