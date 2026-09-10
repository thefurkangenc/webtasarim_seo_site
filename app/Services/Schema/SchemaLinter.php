<?php

namespace App\Services\Schema;

/**
 * Üretilen @graph'ı Schema.org / Google zengin sonuç beklentilerine karşı
 * denetler. Harici bir servise istek atmaz — kural haritası buradadır;
 * panelde ayrıca "Google Rich Results Test" ve "Schema.org Validator"a
 * tek tıkla gidilir.
 */
class SchemaLinter
{
    /**
     * type => ['required' => [...], 'recommended' => [...]]
     *
     * @var array<string, array{required: list<string>, recommended: list<string>}>
     */
    private const RULES = [
        'Organization' => [
            'required' => ['name', 'url'],
            'recommended' => ['logo', 'sameAs', 'contactPoint'],
        ],
        'Corporation' => [
            'required' => ['name', 'url'],
            'recommended' => ['logo', 'sameAs', 'contactPoint', 'address'],
        ],
        'LocalBusiness' => [
            'required' => ['name', 'url', 'address'],
            'recommended' => ['logo', 'telephone', 'openingHoursSpecification', 'priceRange', 'geo', 'image'],
        ],
        'ProfessionalService' => [
            'required' => ['name', 'url', 'address'],
            'recommended' => ['logo', 'telephone', 'openingHoursSpecification', 'priceRange', 'geo', 'areaServed', 'image'],
        ],
        'WebSite' => [
            'required' => ['name', 'url'],
            'recommended' => ['publisher', 'inLanguage'],
        ],
        'WebPage' => [
            'required' => ['name', 'url'],
            'recommended' => ['isPartOf', 'inLanguage', 'breadcrumb'],
        ],
        'AboutPage' => [
            'required' => ['name', 'url'],
            'recommended' => ['isPartOf', 'inLanguage', 'breadcrumb'],
        ],
        'ContactPage' => [
            'required' => ['name', 'url'],
            'recommended' => ['isPartOf', 'inLanguage', 'breadcrumb'],
        ],
        'CollectionPage' => [
            'required' => ['name', 'url'],
            'recommended' => ['isPartOf', 'inLanguage', 'breadcrumb'],
        ],
        'BreadcrumbList' => [
            'required' => ['itemListElement'],
            'recommended' => [],
        ],
        'Service' => [
            'required' => ['name', 'provider'],
            'recommended' => ['description', 'areaServed', 'serviceType'],
        ],
        'BlogPosting' => [
            'required' => ['headline', 'image', 'datePublished', 'publisher', 'author'],
            'recommended' => ['dateModified', 'description', 'mainEntityOfPage'],
        ],
        'FAQPage' => [
            'required' => ['mainEntity'],
            'recommended' => [],
        ],
        'Person' => [
            'required' => ['name'],
            'recommended' => [],
        ],
        'ImageObject' => [
            'required' => ['url'],
            'recommended' => [],
        ],
    ];

    /**
     * @param  array<string, mixed>  $graph
     * @return array{errors: list<string>, warnings: list<string>, passed: list<string>, counts: array{errors: int, warnings: int, nodes: int}}
     */
    public function lint(array $graph): array
    {
        $errors = [];
        $warnings = [];
        $passed = [];

        $nodes = $graph['@graph'] ?? [];

        if (! is_array($nodes) || $nodes === []) {
            return $this->result(['Graf boş — hiç düğüm üretilmedi.'], [], []);
        }

        if (($graph['@context'] ?? null) !== 'https://schema.org') {
            $warnings[] = '@context "https://schema.org" olmalı.';
        }

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                $errors[] = 'Düğüm bir nesne değil.';

                continue;
            }

            $type = $this->typeOf($node);

            if ($type === null) {
                $errors[] = 'Bir düğümde "@type" yok.';

                continue;
            }

            $label = $type.($node['name'] ?? null ? ' ("'.$this->short($node['name']).'")' : '');
            $rules = self::RULES[$type] ?? null;

            if ($rules === null) {
                $passed[] = "{$label}: tip tanındı, özel kural yok.";

                continue;
            }

            foreach ($rules['required'] as $field) {
                if ($this->missing($node, $field)) {
                    $errors[] = "{$label}: zorunlu alan eksik — {$field}.";
                }
            }

            foreach ($rules['recommended'] as $field) {
                if ($this->missing($node, $field)) {
                    $warnings[] = "{$label}: önerilen alan eksik — {$field}.";
                }
            }

            $this->lintSpecial($type, $node, $label, $errors, $warnings);

            if (! $this->hasIssuesFor($label, $errors, $warnings)) {
                $passed[] = "{$label}: eksiksiz.";
            }
        }

        return $this->result($errors, $warnings, $passed);
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     */
    private function lintSpecial(string $type, array $node, string $label, array &$errors, array &$warnings): void
    {
        if ($type === 'BreadcrumbList') {
            $items = $node['itemListElement'] ?? [];

            if (! is_array($items) || $items === []) {
                $errors[] = "{$label}: itemListElement boş.";
            }
        }

        if ($type === 'FAQPage') {
            $entities = $node['mainEntity'] ?? [];

            if (! is_array($entities) || $entities === []) {
                $errors[] = "{$label}: en az bir soru gerekli.";

                return;
            }

            foreach ($entities as $i => $q) {
                $n = $i + 1;

                if (blank($q['name'] ?? null)) {
                    $errors[] = "{$label}: {$n}. sorunun metni yok.";
                }

                if (blank(data_get($q, 'acceptedAnswer.text'))) {
                    $errors[] = "{$label}: {$n}. sorunun cevabı yok.";
                }
            }
        }

        if ($type === 'BlogPosting') {
            if (mb_strlen((string) ($node['headline'] ?? '')) > 110) {
                $warnings[] = "{$label}: headline 110 karakteri aşıyor (Google kırpar).";
            }
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function missing(array $node, string $field): bool
    {
        $value = $node[$field] ?? null;

        return $value === null || $value === '' || $value === [];
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function typeOf(array $node): ?string
    {
        $type = $node['@type'] ?? null;

        return is_array($type) ? ($type[0] ?? null) : $type;
    }

    private function short(mixed $value): string
    {
        return mb_substr(trim((string) (is_array($value) ? '' : $value)), 0, 40);
    }

    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     */
    private function hasIssuesFor(string $label, array $errors, array $warnings): bool
    {
        foreach ([...$errors, ...$warnings] as $line) {
            if (str_starts_with($line, $label.':')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     * @param  list<string>  $passed
     * @return array{errors: list<string>, warnings: list<string>, passed: list<string>, counts: array{errors: int, warnings: int, nodes: int}}
     */
    private function result(array $errors, array $warnings, array $passed): array
    {
        return [
            'errors' => array_values(array_unique($errors)),
            'warnings' => array_values(array_unique($warnings)),
            'passed' => array_values(array_unique($passed)),
            'counts' => [
                'errors' => count(array_unique($errors)),
                'warnings' => count(array_unique($warnings)),
                'nodes' => count($passed) + count(array_unique($errors)),
            ],
        ];
    }
}
