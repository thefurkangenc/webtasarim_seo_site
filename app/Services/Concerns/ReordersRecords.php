<?php

namespace App\Services\Concerns;

use Illuminate\Support\Facades\DB;

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
    }

    abstract protected function reorderModel(): string;
}
