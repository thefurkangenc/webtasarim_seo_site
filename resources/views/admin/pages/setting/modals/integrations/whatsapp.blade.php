@php
    $positions = [
        'right' => 'Sağ alt',
        'left' => 'Sol alt',
    ];
@endphp

<x-admin::form.input name="phone" label="WhatsApp numarası" required
    :value="$values['phone'] ?? null"
    placeholder="05xx xxx xx xx"
    maxlength="30" />

<x-admin::form.textarea name="message" label="Hazır mesaj"
    :value="$values['message'] ?? null"
    placeholder="Merhaba, web sitenizden yazıyorum."
    rows="3" />

<x-admin::form.select name="position" label="Balon konumu" required
    :value="$values['position'] ?? 'right'"
    :options="$positions"
    placeholder=""
    wrapper="mb-0" />
