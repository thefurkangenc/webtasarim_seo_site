<?php

namespace App\Contracts;

/**
 * Bir ön yüz adresine çözülebilen model. Menü öğeleri bir kayda
 * (Page / Service / Blog) polimorfik olarak bağlanabilir; URL render anında
 * buradan çözülür, böylece kaydın slug'ı değişse de bağ kopmaz.
 *
 * config/menus.php > linkables listesindeki her model bunu uygular.
 */
interface LinksToPublicPage
{
    /**
     * Kaydın ön yüz adresi. Kayıt yayında değilse ya da adresi
     * çözülemiyorsa null döner — menü o öğeyi göstermez.
     */
    public function publicUrl(): ?string;

    /** Menü öğesi etiketi boş bırakıldığında kullanılacak ad. */
    public function publicLinkLabel(): string;
}
