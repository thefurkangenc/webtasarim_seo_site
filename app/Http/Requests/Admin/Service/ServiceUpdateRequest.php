<?php

namespace App\Http\Requests\Admin\Service;

/**
 * Kurallar ekleme ile aynı; slug benzersizlik kuralı route'taki hizmeti
 * kendiliğinden hariç tutar. Yalnızca izin farklılaşır.
 */
class ServiceUpdateRequest extends ServiceCreateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('service.update');
    }
}
