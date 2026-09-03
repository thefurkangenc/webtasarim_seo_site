<?php

namespace App\Services\Media;

use App\Models\Media\MediaFolder;
use DomainException;
use Illuminate\Database\Eloquent\Collection;

class MediaFolderService
{
    /** Kök klasörler; alt klasörler `children` ile iç içe yüklenir. */
    public function tree(): Collection
    {
        return MediaFolder::query()
            ->whereNull('parent_id')
            ->with('children.children.children')
            ->withCount('media')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
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
