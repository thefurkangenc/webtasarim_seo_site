<?php

namespace App\Services\BrokenLink;

use DOMDocument;

/**
 * HTML içeriğinden bağlantı (`<a href>`) ve görsel (`<img src>`) adreslerini
 * çıkarır. Editörden gelen içerik hiçbir zaman geçerli bir belge değildir
 * (parça HTML, kapanmamış etiket) — bu yüzden libxml hataları bastırılır.
 */
class LinkExtractor
{
    private const TARGETS = [
        ['a', 'href', 'link'],
        ['img', 'src', 'image'],
    ];

    /** @return list<array{url: string, kind: string}> */
    public function extract(?string $html): array
    {
        if (blank($html) || ! str_contains($html, '<')) {
            return [];
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        // meta charset yerine XML bildirimi: loadHTML aksi halde UTF-8'i
        // latin1 sanıp Türkçe karakterleri bozuyor.
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $found = [];

        foreach (self::TARGETS as [$tag, $attribute, $kind]) {
            foreach ($document->getElementsByTagName($tag) as $node) {
                $url = trim($node->getAttribute($attribute));

                // Aynı adres içerikte birden fazla geçebilir; bir kez yeter.
                if ($url !== '') {
                    $found["{$kind}|{$url}"] = ['url' => $url, 'kind' => $kind];
                }
            }
        }

        return array_values($found);
    }
}
