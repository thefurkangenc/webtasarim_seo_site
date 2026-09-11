<?php

namespace App\Contracts;

/**
 * IndexNow ile arama motorlarına bildirilebilen model.
 *
 * `LinksToPublicPage::publicUrl()`'den ayrı bir metot olmasının sebebi:
 * publicUrl() yayında olmayan kayıt için null döner, IndexNow'da ise
 * **yayından çıkarılan / silinen** adresin de bildirilmesi gerekir — motor o
 * adresi yeniden tarayıp 404'ü görür ve dizininden düşürür. Bu yüzden burada
 * adres, yayın durumuna bakılmadan döndürülür.
 *
 * config/indexnow.php > observed_models listesindeki her model bunu uygular.
 */
interface SubmitsToIndexNow
{
    /** Kaydın ön yüz adresi — yayın durumundan bağımsız; çözülemiyorsa null. */
    public function indexNowUrl(): ?string;
}
