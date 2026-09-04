@props([
    'latName' => 'latitude',
    'lngName' => 'longitude',
    'label' => 'Harita konumu',
    'lat' => null,
    'lng' => null,
    'required' => false,
    'wrapper' => 'mb-[20px] md:mb-[25px] last:mb-0',
])

@php
    $apiKey = app(\App\Services\Integration\IntegrationService::class)->mapsApiKey();
    $map = config('settings.map');
    $latValue = old($latName, $lat);
    $lngValue = old($lngName, $lng);
@endphp

<div
    class="{{ $wrapper }}"
    data-map-picker
    data-api-key="{{ $apiKey }}"
    data-default-lat="{{ $map['default_lat'] }}"
    data-default-lng="{{ $map['default_lng'] }}"
    data-default-zoom="{{ $map['default_zoom'] }}"
    data-selected-zoom="{{ $map['selected_zoom'] }}"
>
    @if ($label)
        <x-admin::form.label :required="$required">{{ $label }}</x-admin::form.label>
    @endif

    <p class="text-xs text-gray-500 dark:text-gray-400 mb-[10px]">
        Haritada bir noktaya tıklayın veya işareti sürükleyin; koordinatlar otomatik dolar.
    </p>

    @if ($apiKey)
        <div data-map-canvas
            class="w-full h-[400px] rounded-md border border-gray-200 dark:border-[#172036] mb-[15px]"></div>
    @else
        <div
            class="py-[1rem] px-[1rem] text-warning-500 bg-warning-50 border border-warning-200 dark:bg-[#15203c] dark:border-[#15203c] rounded-md mb-[15px] text-sm">
            Google Maps açık değil. Haritayı kullanmak için
            <a href="{{ route('admin.setting.edit', 'integrations') }}"
                class="text-black dark:text-white underline hover:text-primary-500">Entegrasyonlar</a>
            sekmesinden Google Maps’i aktif edin. Koordinatları yine de elle yazabilirsiniz.
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[15px]">
        <x-admin::form.input
            :name="$latName"
            label="Enlem"
            :value="$latValue"
            placeholder="Örn. 41.0082"
            wrapper=""
            data-map-lat
            autocomplete="off"
        />
        <x-admin::form.input
            :name="$lngName"
            label="Boylam"
            :value="$lngValue"
            placeholder="Örn. 28.9784"
            wrapper=""
            data-map-lng
            autocomplete="off"
        />
    </div>
</div>
