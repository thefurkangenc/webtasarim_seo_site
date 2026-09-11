<?php

namespace App\Http\Requests\Admin\Analytics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Liste ekranının "son N gün görüntüleme" isteği. Kimlikler tek bir sorgu
 * parametresinde virgülle gelir — tarayıcı tarafı `http.get` dizileri zaten
 * virgülle birleştiriyor.
 */
class PageViewsRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['page', 'blog', 'service'])],
            'ids' => ['required', 'string', 'max:2000', 'regex:/^\d+(,\d+)*$/'],
            'days' => ['nullable', Rule::in(['7', '28', '90'])],
        ];
    }

    /** @return list<int> */
    public function ids(): array
    {
        return array_map('intval', explode(',', $this->string('ids')->toString()));
    }

    public function days(): int
    {
        return (int) ($this->input('days') ?: 28);
    }
}
