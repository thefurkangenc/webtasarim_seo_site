<?php

namespace App\Models\Media;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'name', 'sort_order'])]
class MediaFolder extends Model
{
    use LogsActivity;

    /** Log modül anahtarı: bu model kendi adıyla değil 'media' altında toplanır. */
    public function activityLogName(): string
    {
        return 'media';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    public function breadcrumb(): Collection
    {
        $chain = new Collection([$this]);

        for ($folder = $this->parent; $folder; $folder = $folder->parent) {
            $chain->prepend($folder);
        }

        return $chain;
    }

    /** Dosya yöneticisi ızgarasının klasör kartı/satırı için tek tip gövde. */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'media_count' => $this->media_count ?? 0,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
