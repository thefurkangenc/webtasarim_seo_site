<?php

namespace App\Http\Requests\Admin\Gallery;

/**
 * Kurallar ekleme ile aynı; slug benzersizlik kuralı route'taki galeriyi
 * kendiliğinden hariç tutar. Yalnızca izin farklılaşır.
 */
class GalleryUpdateRequest extends GalleryCreateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gallery.update');
    }
}
