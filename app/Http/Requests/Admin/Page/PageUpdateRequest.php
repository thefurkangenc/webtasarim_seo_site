<?php

namespace App\Http\Requests\Admin\Page;

/**
 * Kurallar ekleme ile aynı; slug ve üst sayfa kuralları route'taki sayfayı
 * kendiliğinden hesaba katar (bkz. PageCreateRequest::parentRule()).
 * Yalnızca izin farklılaşır.
 */
class PageUpdateRequest extends PageCreateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('page.update');
    }
}
