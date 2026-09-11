<?php

namespace App\Contracts;

/**
 * Sidebar menü öğesinin yanında rozet gösteren servis.
 *
 * config/admin-menu.php'de öğeye `'badge' => HealthService::class` yazılır;
 * MenuService sınıfı container'dan çözüp bu metodu çağırır. Config cache'e
 * alınabilsin diye closure değil sınıf adı tutulur.
 *
 * Rozet HER SAYFA YÜKLEMESİNDE hesaplanır — bu metot ağır iş yapmamalı,
 * yalnızca hazır/cache'lenmiş bir değeri okumalıdır.
 */
interface ProvidesMenuBadge
{
    /**
     * Rozet yoksa null; varsa ['count' => int, 'status' => 'critical'|'warning'].
     *
     * @return array{count: int, status: string}|null
     */
    public function menuBadge(): ?array;
}
