<?php

namespace App\Services\Popup;

use App\Models\Popup\Popup;
use App\Services\Concerns\ManagesAudience;
use App\Services\Notice\NoticeResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PopupService
{
    use ManagesAudience;

    public function __construct(private readonly NoticeResolver $notices) {}

    /** @param  array<string, mixed>  $filters */
    public function list(array $filters): LengthAwarePaginator
    {
        return Popup::query()
            ->with('media')
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($query) => $query->where('title', 'like', "%{$term}%")
                    ->orWhere('heading', 'like', "%{$term}%")
            ))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Popup $row) => $row->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Popup $popup): array
    {
        return [
            'popup' => $popup,
            ...$this->notices->options(),
        ];
    }

    public function create(array $data): Popup
    {
        $popup = Popup::create($this->attributes($data));
        $popup->syncMedia($data['image_media_id'] ?? null, 'image');

        return $popup;
    }

    public function update(Popup $popup, array $data): Popup
    {
        $popup->update($this->attributes($data));
        $popup->syncMedia($data['image_media_id'] ?? null, 'image');

        return $popup;
    }

    public function delete(Popup $popup): void
    {
        $popup->delete();
    }

    /** @param  array<string, mixed>  $data */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'heading' => $data['heading'] ?? null,
            'body' => $data['body'] ?? null,
            'button_label' => $data['button_label'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'collect_email' => filter_var($data['collect_email'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'delay_seconds' => (int) ($data['delay_seconds'] ?? 2),
            ...$this->audienceAttributes($data),
        ];
    }
}
