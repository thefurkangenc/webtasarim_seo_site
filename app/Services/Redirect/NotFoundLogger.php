<?php

namespace App\Services\Redirect;

use App\Models\Redirect\NotFoundLog;
use App\Support\UrlPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 404 alan yolları kaydeder. Aynı yol tekrar 404 alırsa sayaç artar.
 *
 * Bir hata işleyicisinin içinden çağrıldığı için asla istisna fırlatmaz —
 * DB erişilemezse sessizce vazgeçer.
 */
class NotFoundLogger
{
    public function record(Request $request): void
    {
        $path = UrlPath::normalize($request->path());

        if ($path === '' || $this->ignored($path)) {
            return;
        }

        $now = now();
        $meta = [
            'last_seen_at' => $now,
            'last_referer' => $this->clip($request->headers->get('referer'), 2000),
            'last_user_agent' => $this->clip($request->userAgent(), 500),
            'last_ip' => $request->ip(),
        ];

        try {
            // Yarış koşulunda çift satır oluşmasın: önce satırı garanti et,
            // sonra sayacı atomik artır.
            $log = NotFoundLog::firstOrCreate(
                ['path' => $path],
                ['hits' => 0, 'first_seen_at' => $now] + $meta,
            );

            NotFoundLog::whereKey($log->id)->update([
                'hits' => DB::raw('hits + 1'),
                // Çözülmüş bir yol yeniden 404 alıyorsa yönlendirme silinmiş
                // ya da bozulmuş demektir — tekrar gündeme gelsin.
                'resolved' => false,
                ...$meta,
            ]);
        } catch (\Throwable) {
            // Loglama 404 yanıtını geciktirmesin / bozmasın.
        }
    }

    private function ignored(string $path): bool
    {
        $first = explode('/', $path)[0];

        if (in_array($first, config('redirects.ignore_prefixes', []), true)) {
            return true;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension !== '' && in_array($extension, config('redirects.ignore_extensions', []), true);
    }

    private function clip(?string $value, int $max): ?string
    {
        return $value === null ? null : mb_substr($value, 0, $max);
    }
}
