<?php

namespace App\Http\Requests\Admin\SocialLink;

class SocialLinkUpdateRequest extends SocialLinkCreateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('social-link.update');
    }
}
