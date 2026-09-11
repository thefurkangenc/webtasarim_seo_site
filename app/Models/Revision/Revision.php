<?php

namespace App\Models\Revision;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Bir kaydın değiştirilmeden önceki hali. Yazma işi RevisionService'e
 * aittir; revizyonlar elle düzenlenmez.
 */
class Revision extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'changed_keys' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** config/revisions.php'deki modül anahtarı (page, blog...). */
    public function moduleKey(): ?string
    {
        foreach (config('revisions.models', []) as $key => $meta) {
            if ($meta['class'] === $this->revisionable_type) {
                return $key;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function moduleMeta(): array
    {
        $key = $this->moduleKey();

        return [
            'key' => $key,
            'label' => $key ? config("revisions.models.{$key}.label") : $this->revisionable_type,
            'icon' => $key ? config("revisions.models.{$key}.icon", 'history') : 'history',
        ];
    }

    /** Kaydın düzenleme adresi — kayıt silinmişse null. */
    public function editUrl(): ?string
    {
        $key = $this->moduleKey();
        $route = $key ? config("revisions.models.{$key}.edit_route") : null;

        return $route && $this->revisionable ? route($route, $this->revisionable_id) : null;
    }

    /**
     * Bu sürümden sonra değişen alanların Türkçe adları.
     *
     * @return array<int, string>
     */
    public function changedLabels(): array
    {
        $labels = config('revisions.labels');

        // Nokta içeren anahtarlar (seo.meta_title) config()'in yol
        // çözümlemesine takılır; dizi doğrudan okunur.
        return array_map(fn (string $key) => $labels[$key] ?? $key, $this->changed_keys ?? []);
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'module' => $this->moduleMeta(),
            'subject_id' => $this->revisionable_id,
            'subject_exists' => $this->revisionable !== null,
            'edit_url' => $this->editUrl(),
            'user' => $this->user?->name,
            'changed' => $this->changedLabels(),
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
            'created_for_humans' => $this->created_at?->diffForHumans(),
        ];
    }
}
