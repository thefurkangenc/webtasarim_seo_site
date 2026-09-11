<?php

namespace App\Http\Requests\Admin\Project;

/**
 * Kurallar ekleme ile aynı; slug benzersizlik kuralı route'taki projeyi
 * kendiliğinden hariç tutar. Yalnızca izin farklılaşır.
 */
class ProjectUpdateRequest extends ProjectCreateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.update');
    }
}
