<?php

namespace App\Services\Schema;

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;
use App\Support\SchemaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Panelin "üretileni gör + denetle" ekranının beyni: bir ön yüz adresini
 * SchemaContext'e çevirir, @graph'ı ürettirir ve linter'dan geçirir.
 */
class SchemaInspector
{
    public function __construct(
        private readonly SchemaGraphBuilder $builder,
        private readonly SchemaLinter $linter,
    ) {}

    /**
     * @return array{
     *     target: array{url: string, path: string, kind: string, resolved: bool},
     *     graph: array<string, mixed>,
     *     json: string,
     *     lint: array<string, mixed>
     * }
     */
    public function report(string $path): array
    {
        [$context, $resolved] = $this->contextFor($path);
        $graph = $this->builder->build($context);

        return [
            'target' => [
                'url' => $context->url,
                'path' => '/'.ltrim($this->normalize($path), '/'),
                'kind' => $context->kind,
                'resolved' => $resolved,
            ],
            'graph' => $graph,
            'json' => json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            'lint' => $this->linter->lint($graph),
        ];
    }

    /**
     * Doğrulama ekranındaki hazır sayfa listesi.
     *
     * @return list<array{label: string, items: list<array{label: string, url: string}>}>
     */
    public function samples(): array
    {
        $groups = [
            ['label' => 'Sabit sayfalar', 'items' => [
                ['label' => 'Ana Sayfa', 'url' => route('anasayfa')],
                ['label' => 'Hakkımızda', 'url' => route('hakkimizda')],
                ['label' => 'Hizmetler', 'url' => route('hizmetler')],
                ['label' => 'Blog', 'url' => route('blog')],
                ['label' => 'İletişim', 'url' => route('iletisim')],
                ['label' => 'KVKK Aydınlatma Metni', 'url' => route('kvkk')],
                ['label' => 'Çerez Politikası', 'url' => route('cerez-politikasi')],
            ]],
        ];

        $services = Service::query()
            ->where('status', Service::STATUS_PUBLISHED)
            ->with(['regions' => fn ($q) => $q->where('is_active', true)->limit(1)])
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $serviceItems = [];

        foreach ($services as $service) {
            $serviceItems[] = [
                'label' => $service->renderGeneric()['title'],
                'url' => route('hizmetler.show', $service->slug),
            ];

            if ($region = $service->regions->first()) {
                $serviceItems[] = [
                    'label' => $service->renderFor($region)['title'].' — '.$region->name.' (bölgeli)',
                    'url' => route('hizmetler.show-region', [$service->slug, $region->slug]),
                ];
            }
        }

        if ($serviceItems !== []) {
            $groups[] = ['label' => 'Hizmetler', 'items' => $serviceItems];
        }

        $posts = Blog::query()
            ->where('status', Blog::STATUS_PUBLISHED)
            ->orderByDesc('published_at')
            ->limit(8)
            ->get(['id', 'title', 'slug']);

        if ($posts->isNotEmpty()) {
            $groups[] = ['label' => 'Blog yazıları', 'items' => $posts
                ->map(fn (Blog $p) => ['label' => $p->title, 'url' => route('blog.show', $p->slug)])
                ->all()];
        }

        $pages = Page::query()->visible()->orderBy('path')->limit(12)->get(['id', 'title', 'path']);

        if ($pages->isNotEmpty()) {
            $groups[] = ['label' => 'Dinamik sayfalar', 'items' => $pages
                ->map(fn (Page $p) => ['label' => $p->title, 'url' => $p->url()])
                ->all()];
        }

        return $groups;
    }

    /**
     * Bir ön yüz adresini SchemaContext'e çevirir. İkinci eleman: adres
     * gerçekten bir route'a/kayıta oturdu mu.
     *
     * @return array{0: SchemaContext, 1: bool}
     */
    private function contextFor(string $path): array
    {
        $path = $this->normalize($path);
        $url = url($path);
        $request = Request::create($url);

        try {
            $route = Route::getRoutes()->match($request);
        } catch (\Throwable) {
            return [SchemaContext::generic(null, $url), false];
        }

        $name = $route->getName();
        $slug = $route->parameter('slug');

        return match ($name) {
            'anasayfa' => [SchemaContext::home(), true],
            'hakkimizda' => [SchemaContext::about(), true],
            'iletisim' => [SchemaContext::contact(), true],
            'hizmetler' => [SchemaContext::collection('Hizmetler', route('hizmetler')), true],
            'blog' => [SchemaContext::collection('Blog', route('blog')), true],
            'kvkk' => [SchemaContext::legal('KVKK Aydınlatma Metni', route('kvkk')), true],
            'cerez-politikasi' => [SchemaContext::legal('Çerez Politikası', route('cerez-politikasi')), true],
            'hizmetler.show' => $this->serviceContext($slug, null),
            'hizmetler.show-region' => $this->serviceContext($slug, $route->parameter('region')),
            'blog.show' => $this->blogContext($slug),
            'sayfa.show' => $this->pageContext((string) $route->parameter('path')),
            default => [SchemaContext::generic(null, $url), false],
        };
    }

    /**
     * @return array{0: SchemaContext, 1: bool}
     */
    private function serviceContext(?string $slug, ?string $regionSlug): array
    {
        $service = Service::where('slug', $slug)
            ->with(['seo.ogMedia', 'faqs', 'regions' => fn ($q) => $q->where('is_active', true)])
            ->first();

        if (! $service) {
            return [SchemaContext::generic(null, url('hizmetler/'.$slug)), false];
        }

        $region = $regionSlug ? $service->regions->firstWhere('slug', $regionSlug) : null;

        if ($regionSlug && ! $region) {
            return [SchemaContext::service($service), true];
        }

        return [SchemaContext::service($service, $region), true];
    }

    /**
     * @return array{0: SchemaContext, 1: bool}
     */
    private function blogContext(?string $slug): array
    {
        $blog = Blog::where('slug', $slug)->with(['media', 'author:id,name', 'tags', 'faqs', 'seo.ogMedia'])->first();

        return $blog
            ? [SchemaContext::blogPosting($blog), true]
            : [SchemaContext::generic(null, url('blog/'.$slug)), false];
    }

    /**
     * @return array{0: SchemaContext, 1: bool}
     */
    private function pageContext(string $path): array
    {
        $page = Page::where('path', $path)->with(['media', 'seo.ogMedia', 'faqs'])->first();

        return $page
            ? [SchemaContext::page($page), true]
            : [SchemaContext::generic(null, url($path)), false];
    }

    private function normalize(string $path): string
    {
        $path = trim($path);

        if (str_contains($path, '://')) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        return '/'.trim($path, '/');
    }
}
