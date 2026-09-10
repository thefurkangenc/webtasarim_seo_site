<?php

namespace App\Http\Requests\Admin\Redirect;

/**
 * Kurallar ekleme ile aynı; `from_path` benzersizlik kuralı route'taki
 * kaydı zaten hariç tutar. Yalnızca izin farklılaşır.
 */
class RedirectUpdateRequest extends RedirectStoreRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('redirect.update');
    }
}
