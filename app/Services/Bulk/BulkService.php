<?php

namespace App\Services\Bulk;

use App\Support\Activity;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Liste ekranlarındaki toplu işlemlerin tek motoru.
 *
 * Modül başına servis/controller çoğaltılmaz: hangi modülde hangi işlemin
 * olduğu config/bulk-actions.php'de yazar, burada yalnızca üç davranış
 * vardır — alan güncelle, etiket ekle, sil.
 *
 * Kayıtlar normal `save()` ile yazılır (Lead modülündeki `saveQuietly`
 * kalıbından bilerek ayrılır): site haritası ve IndexNow gözlemcileri model
 * olaylarına bağlı, revizyon geçmişi de öyle. Toplu bir "yayınla" bunları
 * atlarsa adresler motorlara bildirilmez ve işlem geri alınamaz olur.
 * Bedeli kayıt başına bir log satırıdır; bu yüzden seçim `max` ile sınırlı.
 */
class BulkService
{
    /**
     * @param  array<int, int>  $ids
     * @return array{count: int, message: string}
     */
    public function run(string $module, string $action, array $ids, ?string $value = null): array
    {
        $config = config("bulk-actions.modules.{$module}");
        $definition = $config['actions'][$action] ?? null;

        if ($definition === null) {
            throw new DomainException('Bilinmeyen toplu işlem.');
        }

        $records = $config['model']::query()->whereKey($ids)->get();

        if ($records->isEmpty()) {
            throw new DomainException('Seçili kayıt bulunamadı.');
        }

        $count = DB::transaction(fn () => match ($definition['type']) {
            'update' => $this->update($records, $definition, $value),
            'tags' => $this->tags($records, $value),
            'delete' => $this->delete($records, $config['service']),
            default => throw new DomainException('Bilinmeyen toplu işlem türü.'),
        });

        $message = $count.' '.$config['noun'].' için “'.$definition['label'].'” uygulandı.';

        Activity::record(
            logName: $module,
            event: $definition['type'] === 'delete' ? 'bulk_delete' : 'bulk_update',
            description: $message,
            subjectLabel: config("bulk-actions.modules.{$module}.noun"),
            properties: ['new' => ['action' => $action, 'value' => $value, 'ids' => $records->modelKeys()]],
        );

        return ['count' => $count, 'message' => $message];
    }

    /**
     * Arayüzün ihtiyacı olan işlem tanımları: etiket, ikon, değer alanı ve
     * varsa seçenekleri. Blade bileşeni butonları bundan basar.
     *
     * @return array<int, array<string, mixed>>
     */
    public function options(string $module): array
    {
        return collect(config("bulk-actions.modules.{$module}.actions", []))
            ->map(fn (array $definition, string $key) => [
                'key' => $key,
                'label' => $definition['label'],
                'icon' => $definition['icon'],
                'danger' => $definition['danger'] ?? false,
                'input' => $definition['input'] ?? null,
                'placeholder' => $definition['placeholder'] ?? null,
                'options' => $this->choices($definition),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Model>  $records
     * @param  array<string, mixed>  $definition
     */
    private function update($records, array $definition, ?string $value): int
    {
        $attributes = $definition['values'] ?? [];

        if (isset($definition['field'])) {
            if (! filled($value)) {
                throw new DomainException('Uygulanacak değeri seçin.');
            }

            $attributes[$definition['field']] = $value;
        }

        foreach ($records as $record) {
            $record->fill($attributes)->save();
        }

        return $records->count();
    }

    /** Etiketler EKLENİR; kaydın mevcut etiketleri korunur. */
    private function tags($records, ?string $value): int
    {
        $names = collect(explode(',', (string) $value))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->all();

        if ($names === []) {
            throw new DomainException('En az bir etiket yazın.');
        }

        foreach ($records as $record) {
            $record->syncTags([...$record->tagNames(), ...$names]);
        }

        return $records->count();
    }

    /** Silme modülün kendi servisine bırakılır: ilişkiler orada temizleniyor. */
    private function delete($records, string $service): int
    {
        $handler = app($service);

        foreach ($records as $record) {
            $handler->delete($record);
        }

        return $records->count();
    }

    /**
     * Değer alanı bir liste bekliyorsa seçenekleri okur.
     *
     * @param  array<string, mixed>  $definition
     * @return array<int|string, string>|null
     */
    private function choices(array $definition): ?array
    {
        if (! isset($definition['options_from'])) {
            return null;
        }

        [$model, $column] = $definition['options_from'];

        return $model::query()->orderBy($column)->pluck($column, 'id')->all();
    }
}
