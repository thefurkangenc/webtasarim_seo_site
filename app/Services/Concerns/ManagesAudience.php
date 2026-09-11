<?php

namespace App\Services\Concerns;

trait ManagesAudience
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function audienceAttributes(array $data): array
    {
        $selected = ($data['audience'] ?? 'all') === 'selected';

        return [
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'audience' => $data['audience'] ?? 'all',
            'page_ids' => $selected ? array_values(array_map('intval', $data['page_ids'] ?? [])) : [],
            'blog_ids' => $selected ? array_values(array_map('intval', $data['blog_ids'] ?? [])) : [],
            'service_ids' => $selected ? array_values(array_map('intval', $data['service_ids'] ?? [])) : [],
        ];
    }
}
