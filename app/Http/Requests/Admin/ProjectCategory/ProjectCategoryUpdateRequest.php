<?php

namespace App\Http\Requests\Admin\ProjectCategory;

/**
 * Kurallar ekleme ile aynı; slug benzersizlik kuralı route'taki kategoriyi
 * kendiliğinden hariç tutar. Yalnızca izin farklılaşır.
 */
class ProjectCategoryUpdateRequest extends ProjectCategoryCreateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project-category.update');
    }
}
