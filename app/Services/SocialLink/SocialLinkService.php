<?php

namespace App\Services\SocialLink;

use App\Models\SocialLink\SocialLink;
use App\Services\Concerns\ReordersRecords;
use Illuminate\Support\Facades\DB;

class SocialLinkService
{
    use ReordersRecords;

    /** @return array<int, array<string, mixed>> */
    public function list(): array
    {
        return SocialLink::query()
            ->with('media')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SocialLink $link) => $link->toPayload())
            ->all();
    }

    public function create(array $data): SocialLink
    {
        return DB::transaction(function () use ($data) {
            $link = SocialLink::create($this->attributes($data));
            $link->syncMedia($data['icon_media_id'] ?? null, 'icon');

            return $link->load('media');
        });
    }

    public function update(SocialLink $link, array $data): SocialLink
    {
        return DB::transaction(function () use ($link, $data) {
            $link->update($this->attributes($data));
            $link->syncMedia($data['icon_media_id'] ?? null, 'icon');

            return $link->load('media');
        });
    }

    public function delete(SocialLink $link): void
    {
        DB::transaction(function () use ($link) {
            $link->syncMedia(null, 'icon');
            $link->delete();
        });
    }

    /** @return array<string, string> */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'url' => $data['url'],
        ];
    }

    protected function reorderModel(): string
    {
        return SocialLink::class;
    }
}
