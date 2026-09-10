<?php

namespace App\Services\Concerns;

use App\Support\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sürükle-bırak sıralama modunun kaydettiği sırayı işler.
 *
 * Kullanan servis reorderModel()'i tanımlar:
 *
 *   protected function reorderModel(): string { return BlogCategory::class; }
 *
 * Controller'da tek satır:
 *
 *   public function reorder(ReorderRequest $request): JsonResponse
 *   {
 *       $this->service->reorder($request->validated('ids'));
 *       return $this->success('Sıralama güncellendi.');
 *   }
 */
trait ReordersRecords
{
    /** @param  array<int, int>  $ids  Yeni sırayla verilmiş kimlikler */
    public function reorder(array $ids): void
    {
        $model = $this->reorderModel();

        DB::transaction(function () use ($model, $ids) {
            foreach (array_values($ids) as $index => $id) {
                $model::whereKey($id)->update(['sort_order' => $index]);
            }
        });

        // Sorgu kurucusu Eloquent olayı tetiklemez, dolayısıyla LogsActivity
        // burayı göremez. Ayrıca satır satır loglamak da istemeyiz — tek bir
        // özet kayıt yazılır.
        $instance = new $model;

        Activity::record(
            logName: method_exists($instance, 'activityLogName')
                ? $instance->activityLogName()
                : Str::kebab(class_basename($model)),
            event: 'reorder',
            description: count($ids).' kayıt yeniden sıralandı.',
            properties: ['new' => ['ids' => array_values($ids)]],
        );
    }

    abstract protected function reorderModel(): string;
}
