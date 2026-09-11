<?php

namespace App\Services\Announcement;

use App\Models\Announcement\Announcement;
use App\Services\Concerns\ManagesAudience;
use App\Services\Notice\NoticeResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnnouncementService
{
    use ManagesAudience;

    public function __construct(private readonly NoticeResolver $notices) {}

    /** @param  array<string, mixed>  $filters */
    public function list(array $filters): LengthAwarePaginator
    {
        return Announcement::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($query) => $query->where('title', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%")
            ))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Announcement $row) => $row->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Announcement $announcement): array
    {
        return [
            'announcement' => $announcement,
            ...$this->notices->options(),
            'tones' => config('notices.tones'),
        ];
    }

    public function create(array $data): Announcement
    {
        return Announcement::create($this->attributes($data));
    }

    public function update(Announcement $announcement, array $data): Announcement
    {
        $announcement->update($this->attributes($data));

        return $announcement;
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }

    /** @param  array<string, mixed>  $data */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'message' => $data['message'],
            'button_label' => $data['button_label'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'tone' => $data['tone'] ?? 'primary',
            ...$this->audienceAttributes($data),
        ];
    }
}
