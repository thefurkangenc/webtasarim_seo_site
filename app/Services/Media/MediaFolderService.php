<?php

namespace App\Services\Media;

use App\Models\Media\MediaFolder;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class MediaFolderService
{
    /**
     * Kök klasörler; alt klasörler `children` ile iç içe yüklenir. "Taşı"
     * diyalogundaki klasör ağacı bunu kullanır — grid'in kendisi artık
     * `children()` ile tek seviye çeker, tüm ağacı bir kerede istemez.
     */
    public function tree(): Collection
    {
        return MediaFolder::query()
            ->whereNull('parent_id')
            ->with('children.children.children.children.children')
            ->withCount('media')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** Verilen klasörün doğrudan alt klasörleri — grid'deki klasör kartları bunu kullanır. */
    public function children(?int $parentId): BaseCollection
    {
        return MediaFolder::query()
            ->where('parent_id', $parentId)
            ->withCount('media')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (MediaFolder $folder) => $folder->toPayload());
    }

    public function create(array $data): MediaFolder
    {
        return MediaFolder::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function update(MediaFolder $folder, array $data): MediaFolder
    {
        if (array_key_exists('parent_id', $data)) {
            $this->guardMove($folder, $data['parent_id']);
        }

        $folder->update(array_intersect_key($data, array_flip(['name', 'parent_id', 'sort_order'])));

        return $folder;
    }

    public function delete(MediaFolder $folder): void
    {
        if ($folder->children()->exists()) {
            throw new DomainException('İçinde alt klasör bulunan bir klasör silinemez.');
        }

        if ($folder->media()->exists()) {
            throw new DomainException('İçinde dosya bulunan bir klasör silinemez. Önce dosyaları taşıyın veya silin.');
        }

        $folder->delete();
    }

    /**
     * Çoklu seçimde silme — her klasör güvenlik kuralından tek tek geçer,
     * dolu olan atlanır (toplu işlem yarıda kesilmez, atlananlar raporlanır).
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: int, skipped: array<int, array{name: string, reason: string}>}
     */
    public function deleteMany(array $ids): array
    {
        $deleted = 0;
        $skipped = [];

        foreach (MediaFolder::query()->whereIn('id', $ids)->get() as $folder) {
            try {
                $this->delete($folder);
                $deleted++;
            } catch (DomainException $e) {
                $skipped[] = ['name' => $folder->name, 'reason' => $e->getMessage()];
            }
        }

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }

    /**
     * Çoklu seçimde taşıma — döngü/kendi-içine-taşıma koruması her klasör
     * için ayrı ayrı uygulanır, ihlal eden atlanır.
     *
     * @param  array<int, int>  $ids
     * @return array{moved: int, skipped: array<int, array{name: string, reason: string}>}
     */
    public function moveMany(array $ids, ?int $targetFolderId): array
    {
        $moved = 0;
        $skipped = [];

        foreach (MediaFolder::query()->whereIn('id', $ids)->get() as $folder) {
            try {
                $this->update($folder, ['parent_id' => $targetFolderId]);
                $moved++;
            } catch (DomainException $e) {
                $skipped[] = ['name' => $folder->name, 'reason' => $e->getMessage()];
            }
        }

        return ['moved' => $moved, 'skipped' => $skipped];
    }

    /** Bir klasör kendi altına ya da kendi içine taşınamaz. */
    private function guardMove(MediaFolder $folder, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($parentId === $folder->id) {
            throw new DomainException('Bir klasör kendi içine taşınamaz.');
        }

        for ($parent = MediaFolder::find($parentId); $parent; $parent = $parent->parent) {
            if ($parent->id === $folder->id) {
                throw new DomainException('Bir klasör kendi alt klasörünün içine taşınamaz.');
            }
        }
    }
}
