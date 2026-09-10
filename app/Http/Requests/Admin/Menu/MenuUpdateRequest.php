<?php

namespace App\Http\Requests\Admin\Menu;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Menü konumunun kendisi (header, footer sütunu) — yalnızca ön yüzde görünen
 * başlık düzenlenir. Konum eklenip silinmez.
 */
class MenuUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('menu.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['title' => 'başlık'];
    }
}
