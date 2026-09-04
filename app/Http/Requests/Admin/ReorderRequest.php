<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Sürükle-bırak sıralama isteğinin tek kaynağı — tüm modüllerin `reorder`
 * uç noktası bunu kullanır, modül başına ayrı Request açılmaz.
 */
class ReorderRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ];
    }
}
