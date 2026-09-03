<?php

namespace App\Jobs\Ai;

use App\Models\Ai\AiGeneration;
use App\Services\Ai\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * İçerik üretimini kuyrukta çalıştırır.
 *
 * Yeniden denenmez: yapay zeka istekleri ücretlidir ve aynı hata genelde
 * tekrar eder. Hata kullanıcıya ai_generations.error üzerinden gösterilir.
 */
class GenerateContentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout;

    public function __construct(public int $generationId, int $providerTimeout = 180)
    {
        // Sağlayıcının HTTP zaman aşımından uzun olmalı, yoksa iş önce ölür.
        $this->timeout = $providerTimeout + 30;
    }

    public function handle(AiService $service): void
    {
        $generation = AiGeneration::find($this->generationId);

        if ($generation && ! $generation->isFinished()) {
            $service->run($generation);
        }
    }

    /** İş kuyrukta çökerse (zaman aşımı gibi) kayıt asılı kalmasın. */
    public function failed(?Throwable $exception): void
    {
        AiGeneration::where('id', $this->generationId)
            ->whereNotIn('status', [AiGeneration::STATUS_COMPLETED, AiGeneration::STATUS_FAILED])
            ->update([
                'status' => AiGeneration::STATUS_FAILED,
                'error' => $exception?->getMessage() ?? 'Üretim tamamlanamadı.',
            ]);
    }
}
