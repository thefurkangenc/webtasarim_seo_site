<?php

namespace App\Contracts;

/**
 * Adresi değişince otomatik 301 üreten model. RedirectObserver `updated`
 * olayında `redirectableMove()`'u çağırır; eski ve yeni yol farklıysa bir
 * yönlendirme oluşturur (ve varsa eski zinciri yeni hedefe kaydırır).
 *
 * config/redirects.php > auto_from listesindeki her model bunu uygular.
 */
interface RedirectsOnMove
{
    /**
     * Kaydın bu güncellemede adresi değiştiyse eski ve yeni yolu döndürür,
     * değişmediyse null. Yollar normalize edilmeden verilebilir —
     * RedirectService normalize eder.
     *
     * @return array{from: string, to: string}|null
     */
    public function redirectableMove(): ?array;
}
