<?php

namespace App\Http\Requests\Admin\Bulk;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Toplu işlem isteği. Modül route tanımındaki `defaults('module', ...)`
 * ile gelir; izin denetimi route adı üzerinden (blog.bulk, page.bulk...)
 * global middleware'de yapılır.
 */
class BulkRequest extends FormRequest
{
    public function module(): string
    {
        return $this->route()->defaults['module'];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $actions = array_keys(config("bulk-actions.modules.{$this->module()}.actions", []));

        return [
            'ids' => ['required', 'array', 'min:1', 'max:'.config('bulk-actions.max')],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in($actions)],
            // Yalnızca değer isteyen işlemlerde (kategori ata, etiket ekle) dolu gelir.
            'value' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'ids.required' => 'En az bir kayıt seçin.',
            'ids.max' => 'Tek seferde en fazla '.config('bulk-actions.max').' kayıt işlenebilir.',
        ];
    }
}
