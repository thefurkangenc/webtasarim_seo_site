<?php

namespace App\Support;

/**
 * Bu isteğin hangi içerik sayfasına denk geldiği. Duyuru şeridi ve popup
 * "seçili sayfalar" hedefini buna göre çözer.
 */
final class AudienceContext
{
    public function __construct(
        public readonly bool $isHome,
        public readonly ?int $pageId,
        public readonly ?int $blogId,
        public readonly ?int $serviceId,
    ) {}

    /**
     * @param  array<int, int>|null  $pageIds
     * @param  array<int, int>|null  $blogIds
     * @param  array<int, int>|null  $serviceIds
     */
    public function matchesSelected(?array $pageIds, ?array $blogIds, ?array $serviceIds): bool
    {
        return $this->inList($this->pageId, $pageIds)
            || $this->inList($this->blogId, $blogIds)
            || $this->inList($this->serviceId, $serviceIds);
    }

    /** @param  array<int, mixed>|null  $ids */
    private function inList(?int $id, ?array $ids): bool
    {
        if ($id === null || $ids === null) {
            return false;
        }

        return in_array($id, array_map('intval', $ids), true);
    }
}
