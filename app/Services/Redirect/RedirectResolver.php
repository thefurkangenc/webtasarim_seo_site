<?php

namespace App\Services\Redirect;

use App\Models\Redirect\Redirect;
use App\Support\UrlPath;
use Illuminate\Support\Facades\DB;

/**
 * 404 alan bir yol için aktif bir yönlendirme arar.
 *
 * Öncelik: birebir → önek (en uzun kaynak kazanır) → regex (id sırası).
 * Bulunursa isabet sayacı artırılır ve bir "sonuç" nesnesi döner; çağıran
 * (bootstrap/app.php'deki render kancası) bunu HTTP yanıtına çevirir.
 */
class RedirectResolver
{
    public function resolve(string $requestPath): ?RedirectResult
    {
        $path = UrlPath::normalize($requestPath);

        $redirect = $this->matchExact($path)
            ?? $this->matchPrefix($path)
            ?? $this->matchRegex($path);

        if (! $redirect) {
            return null;
        }

        [$target, $status] = $redirect;

        return new RedirectResult($target, $status, $redirect[2]);
    }

    /**
     * Bir isabeti kaydeder. Ayrı bir metot: render kancası önce sonucu alıp
     * yanıtı döndürür, sayacı sonra günceller — istemci beklemesin.
     */
    public function registerHit(int $redirectId): void
    {
        Redirect::whereKey($redirectId)->update([
            'hits' => DB::raw('hits + 1'),
            'last_hit_at' => now(),
        ]);
    }

    /** @return array{0: string, 1: int, 2: int}|null  [hedef, kod, id] */
    private function matchExact(string $path): ?array
    {
        $redirect = Redirect::active()
            ->where('match_type', Redirect::MATCH_EXACT)
            ->where('from_path', $path)
            ->first();

        return $redirect
            ? [$this->targetFor($redirect, $path), $redirect->status_code, $redirect->id]
            : null;
    }

    /** @return array{0: string, 1: int, 2: int}|null */
    private function matchPrefix(string $path): ?array
    {
        // En uzun (en özgül) önek kazanır: /a/b, /a'dan önce denenir.
        $candidates = Redirect::active()
            ->where('match_type', Redirect::MATCH_PREFIX)
            ->orderByRaw('CHAR_LENGTH(from_path) DESC')
            ->get();

        foreach ($candidates as $redirect) {
            $prefix = $redirect->from_path;

            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                $remainder = ltrim(substr($path, strlen($prefix)), '/');
                $target = rtrim($redirect->to_url ?? '', '/');

                if ($remainder !== '' && ! $redirect->isGone()) {
                    $target .= '/'.$remainder;
                }

                return [$target, $redirect->status_code, $redirect->id];
            }
        }

        return null;
    }

    /** @return array{0: string, 1: int, 2: int}|null */
    private function matchRegex(string $path): ?array
    {
        foreach (Redirect::active()->where('match_type', Redirect::MATCH_REGEX)->orderBy('id')->get() as $redirect) {
            $pattern = '#'.str_replace('#', '\#', $redirect->from_path).'#iu';

            // Bozuk bir desen tüm ön yüzü çökertmesin.
            $matched = @preg_match($pattern, $path);

            if ($matched === 1) {
                $target = $redirect->isGone()
                    ? (string) $redirect->to_url
                    : (string) @preg_replace($pattern, (string) $redirect->to_url, $path);

                return [$target, $redirect->status_code, $redirect->id];
            }
        }

        return null;
    }

    private function targetFor(Redirect $redirect, string $path): string
    {
        return (string) $redirect->to_url;
    }
}
