@php
    $positions = [
        'right' => 'Sağ alt',
        'left' => 'Sol alt',
    ];
@endphp

<x-admin::form.input name="phone" label="Telefon numarası" required
    :value="$values['phone'] ?? null"
    placeholder="05xx xxx xx xx"
    maxlength="30" />

<x-admin::form.select name="position" label="Buton konumu" required
    :value="$values['position'] ?? 'left'"
    :options="$positions"
    placeholder=""
    wrapper="mb-0" />
