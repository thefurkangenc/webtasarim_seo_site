<?php

namespace App\Services\Schema;

use App\Models\Blog\Blog;
use App\Models\Media\Media;
use App\Models\Page\Page;
use App\Models\Service\Service;
use App\Models\SocialLink\SocialLink;
use App\Services\Setting\SettingService;
use App\Support\SchemaContext;
use Illuminate\Support\Str;

/**
 * Bir sayfa için standartlara uygun JSON-LD @graph üretir.
 *
 * Çıktı bağlı bir graftır: Organization ve WebSite düğümleri her sayfada
 * sabittir, sayfaya özel düğümler (WebPage / Service / BlogPosting / FAQPage /
 * BreadcrumbList) `@id` çapalarıyla bunlara referans verir. Google'ın önerdiği
 * yapı budur.
 *
 * Kayıt bazında override (`seo` tablosundaki schema_type / schema_json /
 * schema_override) SchemaContext'in modeli üzerinden okunur.
 */
class SchemaGraphBuilder
{
    /** @var array<string, string> */
    private const DAYS = [
        'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday',
        'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday',
    ];

    /** Yerel işletme alanları (adres, saat, fiyat) yalnızca bu türlerde basılır. */
    private const LOCAL_TYPES = ['LocalBusiness', 'ProfessionalService'];

    private string $site;

    public function __construct(private readonly SettingService $settings)
    {
        $this->site = rtrim((string) config('app.url'), '/');
    }

    /**
     * @return array{'@context': string, '@graph': list<array<string, mixed>>}
     */
    public function build(SchemaContext $ctx): array
    {
        $company = $this->settings->getGroup('company');
        $schema = array_replace(config('settings.defaults.schema', []), $this->settings->getGroup('schema'));
        $seo = $this->settings->getGroup('seo');

        $nodes = [
            $this->organization($company, $schema),
            $this->website($company, $schema, $seo),
        ];

        $override = $ctx->model && method_exists($ctx->model, 'schemaOverride')
            ? $ctx->model->schemaOverride()
            : ['type' => null, 'json' => null, 'override' => false];

        if (! $override['override']) {
            foreach ($this->pageNodes($ctx, $company, $schema, $override['type']) as $node) {
                $nodes[] = $node;
            }
        }

        foreach ($this->customNodes($override['json']) as $node) {
            $nodes[] = $node;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_map(fn ($n) => $this->prune($n), $nodes)),
        ];
    }

    /* ------------------------------------------------------------------ *
     | Site geneli düğümler
     * ------------------------------------------------------------------ */

    /**
     * @param  array<string, string|null>  $company
     * @param  array<string, string|null>  $schema
     * @return array<string, mixed>
     */
    private function organization(array $company, array $schema): array
    {
        $type = $schema['business_type'] ?: 'Organization';
        $name = $company['name'] ?: config('app.name');
        $logo = $this->logoObject($company);

        $node = [
            '@type' => $type,
            '@id' => $this->site.'/#organization',
            'name' => $name,
            'legalName' => $company['legal_name'] ?: null,
            'url' => $this->site.'/',
            'description' => $company['short_description'] ?: ($schema['description'] ?? null),
            'telephone' => $company['phone'] ?: null,
            'email' => $company['email'] ?: null,
            'faxNumber' => $company['fax'] ?: null,
            'foundingDate' => $this->year($schema['founding_year'] ?? null),
            'taxID' => $schema['tax_id'] ?? null,
            'knowsLanguage' => 'tr-TR',
            'sameAs' => $this->sameAs($schema),
        ];

        if ($logo) {
            $node['logo'] = $logo;
            $node['image'] = ['@id' => $this->site.'/#logo'];
        }

        if ($address = $this->postalAddress($company)) {
            $node['address'] = $address;
        }

        if ($contactPoint = $this->contactPoint($company)) {
            $node['contactPoint'] = [$contactPoint];
        }

        if (in_array($type, self::LOCAL_TYPES, true)) {
            $node['priceRange'] = $schema['price_range'] ?: null;
            $node['areaServed'] = $this->areaServed($schema['area_served'] ?? null);
            $node['geo'] = $this->geo($company);
            $node['openingHoursSpecification'] = $this->openingHours($schema['opening_hours'] ?? null);
        }

        return $node;
    }

    /**
     * @param  array<string, string|null>  $company
     * @param  array<string, string|null>  $schema
     * @param  array<string, string|null>  $seo
     * @return array<string, mixed>
     */
    private function website(array $company, array $schema, array $seo): array
    {
        $node = [
            '@type' => 'WebSite',
            '@id' => $this->site.'/#website',
            'url' => $this->site.'/',
            'name' => $seo['site_name'] ?: ($company['name'] ?: config('app.name')),
            'inLanguage' => 'tr-TR',
            'publisher' => ['@id' => $this->site.'/#organization'],
        ];

        $template = trim((string) ($schema['search_url'] ?? ''));

        if ($template !== '') {
            $template = str_replace(['{query}', '{search}', '{search_term_string}'], '{search_term_string}', $template);
            $node['potentialAction'] = [[
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->absolute($template),
                ],
                'query-input' => 'required name=search_term_string',
            ]];
        }

        return $node;
    }

    /* ------------------------------------------------------------------ *
     | Sayfaya özel düğümler
     * ------------------------------------------------------------------ */

    /**
     * @param  array<string, string|null>  $company
     * @param  array<string, string|null>  $schema
     * @return list<array<string, mixed>>
     */
    private function pageNodes(SchemaContext $ctx, array $company, array $schema, ?string $overrideType): array
    {
        $nodes = [];
        $webPageType = $overrideType && ! in_array($ctx->kind, [SchemaContext::SERVICE, SchemaContext::BLOG_POSTING], true)
            ? $overrideType
            : $this->webPageType($ctx->kind);

        $webPage = [
            '@type' => $webPageType,
            '@id' => $this->pageUrl($ctx).'#webpage',
            'url' => $this->pageUrl($ctx),
            'name' => $ctx->title ?: ($company['name'] ?: config('app.name')),
            'isPartOf' => ['@id' => $this->site.'/#website'],
            'inLanguage' => 'tr-TR',
        ];

        if (count($ctx->breadcrumbs) >= 2) {
            $webPage['breadcrumb'] = ['@id' => $this->pageUrl($ctx).'#breadcrumb'];
            $nodes[] = $this->breadcrumbList($ctx);
        }

        if (in_array($ctx->kind, [SchemaContext::HOME, SchemaContext::ABOUT, SchemaContext::CONTACT], true)) {
            $webPage['about'] = ['@id' => $this->site.'/#organization'];
        }

        if ($ctx->model instanceof Page || $ctx->model instanceof Blog) {
            $webPage['datePublished'] = $ctx->model->published_at?->toIso8601String() ?: $ctx->model->created_at?->toIso8601String();
            $webPage['dateModified'] = $ctx->model->updated_at?->toIso8601String();
        }

        if ($image = $this->primaryImage($ctx)) {
            $webPage['primaryImageOfPage'] = $image;
        }

        // WebPage her zaman ilk sayfa düğümü olsun (breadcrumb ondan sonra gelir).
        array_unshift($nodes, $webPage);

        if ($ctx->kind === SchemaContext::SERVICE && $ctx->model instanceof Service) {
            $nodes[] = $this->serviceNode($ctx, $schema, $overrideType);
        }

        if ($ctx->kind === SchemaContext::BLOG_POSTING && $ctx->model instanceof Blog) {
            $nodes[] = $this->blogPostingNode($ctx, $overrideType);
        }

        if ($faq = $this->faqNode($ctx)) {
            $nodes[] = $faq;
        }

        return $nodes;
    }

    private function webPageType(string $kind): string
    {
        return match ($kind) {
            SchemaContext::ABOUT => 'AboutPage',
            SchemaContext::CONTACT => 'ContactPage',
            SchemaContext::COLLECTION => 'CollectionPage',
            default => 'WebPage',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function breadcrumbList(SchemaContext $ctx): array
    {
        $items = [];

        foreach ($ctx->breadcrumbs as $index => $crumb) {
            $item = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
            ];

            if (! empty($crumb['url'])) {
                $item['item'] = $crumb['url'];
            }

            $items[] = $item;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->pageUrl($ctx).'#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    /**
     * @param  array<string, string|null>  $schema
     * @return array<string, mixed>
     */
    private function serviceNode(SchemaContext $ctx, array $schema, ?string $overrideType): array
    {
        /** @var Service $service */
        $service = $ctx->model;
        $meta = $ctx->region ? $service->renderFor($ctx->region)['seo'] : $service->renderGeneric()['seo'];

        $area = $ctx->region
            ? ['@type' => 'Place', 'name' => $ctx->region->placeholders()['region']]
            : $this->areaServed($schema['area_served'] ?? null) ?? 'Türkiye';

        return [
            '@type' => $overrideType ?: 'Service',
            '@id' => $this->pageUrl($ctx).'#service',
            'name' => $ctx->title,
            'serviceType' => $service->renderGeneric()['title'],
            'description' => $meta['description'] ?? null,
            'url' => $this->pageUrl($ctx),
            'provider' => ['@id' => $this->site.'/#organization'],
            'areaServed' => $area,
            'mainEntityOfPage' => ['@id' => $this->pageUrl($ctx).'#webpage'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blogPostingNode(SchemaContext $ctx, ?string $overrideType): array
    {
        /** @var Blog $blog */
        $blog = $ctx->model;
        $meta = $blog->seoMeta();

        return [
            '@type' => $overrideType ?: 'BlogPosting',
            '@id' => $this->pageUrl($ctx).'#article',
            'headline' => Str::limit($blog->title, 108, ''),
            'description' => $meta['description'] ?? null,
            'image' => $this->imageList($ctx),
            'datePublished' => $blog->published_at?->toIso8601String() ?: $blog->created_at?->toIso8601String(),
            'dateModified' => $blog->updated_at?->toIso8601String(),
            'author' => $blog->author
                ? ['@type' => 'Person', 'name' => $blog->author->name]
                : ['@id' => $this->site.'/#organization'],
            'publisher' => ['@id' => $this->site.'/#organization'],
            'mainEntityOfPage' => ['@id' => $this->pageUrl($ctx).'#webpage'],
            'articleSection' => $blog->category?->name,
            'keywords' => method_exists($blog, 'tagNames') && $blog->tagNames() !== []
                ? implode(', ', $blog->tagNames())
                : null,
            'inLanguage' => 'tr-TR',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function faqNode(SchemaContext $ctx): ?array
    {
        $model = $ctx->model;

        if (! $model || ! method_exists($model, 'faqs')) {
            return null;
        }

        $faqs = $model->relationLoaded('faqs') ? $model->faqs : $model->faqs()->get();

        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $this->pageUrl($ctx).'#faq',
            'isPartOf' => ['@id' => $this->pageUrl($ctx).'#webpage'],
            'mainEntity' => $faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(strip_tags((string) $faq->answer)),
                ],
            ])->all(),
        ];
    }

    /* ------------------------------------------------------------------ *
     | Kayıt bazında ham JSON
     * ------------------------------------------------------------------ */

    /**
     * @param  array<int|string, mixed>|null  $json
     * @return list<array<string, mixed>>
     */
    private function customNodes(?array $json): array
    {
        if (! $json) {
            return [];
        }

        // Tek bir düğüm nesnesi mi (string anahtarlı) yoksa düğüm listesi mi?
        return array_is_list($json) ? array_values(array_filter($json, 'is_array')) : [$json];
    }

    /* ------------------------------------------------------------------ *
     | Yardımcılar
     * ------------------------------------------------------------------ */

    /**
     * @param  array<string, string|null>  $company
     * @return array<string, mixed>|null
     */
    private function logoObject(array $company): ?array
    {
        $url = $this->mediaUrl($company['logo_media_id'] ?? null);

        return $url ? [
            '@type' => 'ImageObject',
            '@id' => $this->site.'/#logo',
            'url' => $url,
            'contentUrl' => $url,
        ] : null;
    }

    /**
     * @param  array<string, string|null>  $company
     * @return array<string, mixed>|null
     */
    private function postalAddress(array $company): ?array
    {
        if (blank($company['address'] ?? null)) {
            return null;
        }

        return [
            '@type' => 'PostalAddress',
            'streetAddress' => trim(preg_replace('/\s+/', ' ', (string) $company['address'])),
            'addressCountry' => 'TR',
        ];
    }

    /**
     * @param  array<string, string|null>  $company
     * @return array<string, mixed>|null
     */
    private function contactPoint(array $company): ?array
    {
        if (blank($company['phone'] ?? null) && blank($company['email'] ?? null)) {
            return null;
        }

        return [
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'telephone' => $company['phone'] ?: null,
            'email' => $company['email'] ?: null,
            'areaServed' => 'TR',
            'availableLanguage' => ['Turkish'],
        ];
    }

    /**
     * @param  array<string, string|null>  $company
     * @return array<string, mixed>|null
     */
    private function geo(array $company): ?array
    {
        if (blank($company['latitude'] ?? null) || blank($company['longitude'] ?? null)) {
            return null;
        }

        return [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $company['latitude'],
            'longitude' => (float) $company['longitude'],
        ];
    }

    /**
     * @param  array<string, string|null>  $schema
     * @return list<string>
     */
    private function sameAs(array $schema): array
    {
        $links = SocialLink::query()->orderBy('sort_order')->pluck('url')->all();

        foreach (preg_split('/[\r\n,]+/', (string) ($schema['same_as'] ?? '')) as $extra) {
            $extra = trim($extra);

            if ($extra !== '') {
                $links[] = $extra;
            }
        }

        return array_values(array_unique(array_filter($links, fn ($u) => filter_var($u, FILTER_VALIDATE_URL))));
    }

    private function areaServed(?string $value): string|array|null
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $value))));

        if (count($parts) <= 1) {
            return $parts[0] ?? $value;
        }

        return array_map(fn ($name) => ['@type' => 'AdministrativeArea', 'name' => $name], $parts);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function openingHours(?string $json): array
    {
        $data = json_decode((string) $json, true);

        if (! is_array($data)) {
            return [];
        }

        $specs = [];

        foreach (self::DAYS as $key => $day) {
            $row = $data[$key] ?? null;

            if (! is_array($row) || ! empty($row['closed'])) {
                continue;
            }

            $opens = trim((string) ($row['opens'] ?? ''));
            $closes = trim((string) ($row['closes'] ?? ''));

            if ($opens === '' || $closes === '') {
                continue;
            }

            $specs[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $day,
                'opens' => $opens,
                'closes' => $closes,
            ];
        }

        return $specs;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function primaryImage(SchemaContext $ctx): ?array
    {
        $url = $this->modelImage($ctx->model);

        return $url ? [
            '@type' => 'ImageObject',
            '@id' => $this->pageUrl($ctx).'#primaryimage',
            'url' => $url,
            'contentUrl' => $url,
        ] : null;
    }

    /**
     * @return list<string>
     */
    private function imageList(SchemaContext $ctx): array
    {
        $url = $this->modelImage($ctx->model);

        return $url ? [$url] : [];
    }

    private function modelImage(mixed $model): ?string
    {
        if ($model && method_exists($model, 'getFirstMedia') && $media = $model->getFirstMedia('cover')) {
            return $this->absolute($media->url('medium'));
        }

        return null;
    }

    private function mediaUrl(mixed $id): ?string
    {
        if (blank($id)) {
            return null;
        }

        $media = Media::query()->find($id);

        return $media ? $this->absolute($media->url('medium')) : null;
    }

    private function year(?string $value): ?string
    {
        return preg_match('/^\d{4}$/', trim((string) $value)) ? trim((string) $value) : null;
    }

    /**
     * Sayfa adresini `@id` çapası ve `url` alanı için normalize eder: çıplak
     * alan adına "/" ekler ("test" → "test/"), böylece "test/#webpage" olur.
     */
    private function pageUrl(SchemaContext $ctx): string
    {
        $url = $ctx->url;

        return in_array(parse_url($url, PHP_URL_PATH), [null, '', '/'], true)
            ? rtrim($url, '/').'/'
            : $url;
    }

    private function absolute(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return $this->site.'/'.ltrim($url, '/');
    }

    /**
     * null / '' / boş dizi taşıyan anahtarları özyinelemeli olarak atar.
     */
    private function prune(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $clean = [];

        foreach ($value as $key => $item) {
            $item = $this->prune($item);

            if ($item === null || $item === '' || $item === []) {
                continue;
            }

            $clean[$key] = $item;
        }

        return $clean;
    }
}
