<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog\ActivityLog;
use App\Support\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * Modele denetim kaydı yeteneği verir. Eklendiği andan itibaren ekleme,
 * düzenleme ve silme kendiliğinden loglanır:
 *
 *   class Blog extends Model { use LogsActivity; }
 *
 * Eloquent olaylarına bağlandığı için nereden yazıldığı fark etmez —
 * servis, tinker, seeder, hepsi yakalanır. TEK İSTİSNA sorgu kurucusudur
 * (`Model::where(...)->update(...)`), o model olayı tetiklemez; sıralama
 * gibi toplu işlemler bu yüzden servisten elle loglanır.
 *
 * Modelde özelleştirilebilir:
 *   activityLogName()   modül anahtarı (varsayılan: sınıf adından türetilir)
 *   activityLabel()     kaydın okunabilir adı (varsayılan: title/name/question)
 *   activityIgnored()   diff'e girmeyecek ek alanlar
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            Activity::record(
                logName: $model->activityLogName(),
                event: 'created',
                description: $model->activityDescription('eklendi'),
                subject: $model,
                subjectLabel: $model->activityLabel(),
                properties: ['new' => $model->activityAttributes($model->getAttributes())],
            );
        });

        static::updated(function ($model) {
            $changes = $model->activityAttributes($model->getChanges());

            // Yalnızca yok sayılan alanlar değiştiyse (updated_at, sort_order)
            // log yazma — liste anlamsız kayıtlarla dolmasın.
            if ($changes === []) {
                return;
            }

            $before = array_intersect_key(
                $model->activityAttributes($model->getOriginal()),
                $changes,
            );

            Activity::record(
                logName: $model->activityLogName(),
                event: 'updated',
                description: $model->activityDescription('düzenlendi'),
                subject: $model,
                subjectLabel: $model->activityLabel(),
                properties: ['old' => $before, 'new' => $changes],
                changedKeys: array_keys($changes),
            );
        });

        static::deleted(function ($model) {
            Activity::record(
                logName: $model->activityLogName(),
                event: 'deleted',
                description: $model->activityDescription('silindi'),
                subject: $model,
                subjectLabel: $model->activityLabel(),
                // Silinen kaydın tamamı saklanır: log, kaydın son halinin
                // tek kalan kanıtı olabilir.
                properties: ['old' => $model->activityAttributes($model->getAttributes())],
            );
        });
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('created_at');
    }

    /**
     * Modül anahtarı. App\Models\Blog\Blog -> 'blog',
     * App\Models\ServiceRegion\ServiceRegion -> 'service-region'.
     */
    public function activityLogName(): string
    {
        return Str::kebab(class_basename($this));
    }

    /** Kaydın insan tarafından okunabilir adı. */
    public function activityLabel(): ?string
    {
        foreach (['title', 'name', 'question', 'label', 'email'] as $key) {
            if (filled($this->getAttribute($key))) {
                return (string) $this->getAttribute($key);
            }
        }

        return null;
    }

    /** @return array<int, string> */
    public function activityIgnored(): array
    {
        return [];
    }

    private function activityDescription(string $verb): string
    {
        $module = config("activity-log.modules.{$this->activityLogName()}.label")
            ?? class_basename($this);

        $label = $this->activityLabel();

        return $label ? "{$module}: {$label} {$verb}" : "{$module} kaydı {$verb}";
    }

    /**
     * Diff'e girecek alanları süzer. Maskeleme/kırpma mantığı bilinçli olarak
     * burada DEĞİL Activity::sanitize()'da — elle log yazan servislerle aynı
     * korumayı paylaşsın diye.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function activityAttributes(array $attributes): array
    {
        return Activity::sanitize($attributes, $this->activityIgnored());
    }
}
