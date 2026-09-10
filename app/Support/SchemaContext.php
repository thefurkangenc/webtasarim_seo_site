<?php

namespace App\Support;

use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Service\Service;
use App\Models\ServiceRegion\ServiceRegion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;

/**
 * Ön yüzde o an render edilen sayfanın "ne olduğu"nu taşıyan değer nesnesi.
 * SchemaGraphBuilder bunu alıp uygun JSON-LD @graph'ı üretir.
 *
 * Dinamik controller'lar modeliyle birlikte açık bir context geçirir
 * (SchemaContext::blogPosting($blog) gibi); statik route'lar fromRoute()'a
 * düşer ve route adından türetilir.
 */
final class SchemaContext
{
    public const HOME = 'home';

    public const ABOUT = 'about';

    public const CONTACT = 'contact';

    public const LEGAL = 'legal';

    public const COLLECTION = 'collection';

    public const SERVICE = 'service';

    public const BLOG_POSTING = 'blog_posting';

    public const PAGE = 'page';

    public const GENERIC = 'generic';

    /**
     * @param  list<array{name: string, url: string|null}>  $breadcrumbs
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $url,
        public readonly string $title = '',
        public readonly array $breadcrumbs = [],
        public readonly ?Model $model = null,
        public readonly ?ServiceRegion $region = null,
    ) {}

    public static function home(?string $url = null): self
    {
        return new self(self::HOME, $url ?? route('anasayfa'), (string) config('app.name'));
    }

    public static function about(?string $url = null): self
    {
        $url ??= route('hakkimizda');

        return new self(self::ABOUT, $url, 'Hakkımızda', self::trail([['Hakkımızda', $url]]));
    }

    public static function contact(?string $url = null): self
    {
        $url ??= route('iletisim');

        return new self(self::CONTACT, $url, 'İletişim', self::trail([['İletişim', $url]]));
    }

    public static function legal(string $title, ?string $url = null): self
    {
        $url ??= url()->current();

        return new self(self::LEGAL, $url, $title, self::trail([[$title, $url]]));
    }

    public static function collection(string $title, ?string $url = null): self
    {
        $url ??= url()->current();

        return new self(self::COLLECTION, $url, $title, self::trail([[$title, $url]]));
    }

    public static function service(Service $service, ?ServiceRegion $region = null, ?string $url = null): self
    {
        $generic = $service->renderGeneric()['title'];
        $rendered = $region ? $service->renderFor($region)['title'] : $generic;
        $url ??= $region
            ? route('hizmetler.show-region', [$service->slug, $region->slug])
            : route('hizmetler.show', $service->slug);

        $trail = [['Hizmetler', route('hizmetler')]];

        if ($region) {
            $trail[] = [$generic, route('hizmetler.show', $service->slug)];
            $trail[] = [$region->name, $url];
        } else {
            $trail[] = [$rendered, $url];
        }

        return new self(self::SERVICE, $url, $rendered, self::trail($trail), $service, $region);
    }

    public static function blogPosting(Blog $blog, ?string $url = null): self
    {
        $url ??= route('blog.show', $blog->slug);

        return new self(self::BLOG_POSTING, $url, $blog->title, self::trail([
            ['Blog', route('blog')],
            [$blog->title, $url],
        ]), $blog);
    }

    public static function page(Page $page, ?string $url = null): self
    {
        $url ??= $page->url();

        $segments = explode('/', (string) $page->path);
        $last = count($segments) - 1;
        $trail = [];
        $prefix = '';

        foreach ($segments as $index => $segment) {
            $prefix = $prefix === '' ? $segment : "{$prefix}/{$segment}";
            // Yaprak segmenti gerçek sayfa başlığıyla etiketlenir; üst sayfalar
            // (yüklenmediği için) segment adından türer.
            $trail[] = [$index === $last ? $page->title : $segment, url($prefix)];
        }

        return new self(self::PAGE, $url, $page->title, self::trail($trail), $page);
    }

    public static function generic(?string $title = null, ?string $url = null): self
    {
        return new self(self::GENERIC, $url ?? url()->current(), $title ?? (string) config('app.name'));
    }

    /**
     * Route adından statik sayfa context'i türetir. Bilinmeyen route generic'e düşer.
     */
    public static function fromRoute(?Route $route = null): self
    {
        $route ??= request()->route();

        return match ($route?->getName()) {
            'anasayfa' => self::home(),
            'hakkimizda' => self::about(),
            'iletisim' => self::contact(),
            'hizmetler' => self::collection('Hizmetler', route('hizmetler')),
            'blog' => self::collection('Blog', route('blog')),
            'kvkk' => self::legal('KVKK Aydınlatma Metni', route('kvkk')),
            'cerez-politikasi' => self::legal('Çerez Politikası', route('cerez-politikasi')),
            default => self::generic(),
        };
    }

    /**
     * "Ana Sayfa"yı başa ekleyerek kırılım listesini kurar.
     *
     * @param  list<array{0: string, 1: string|null}>  $items
     * @return list<array{name: string, url: string|null}>
     */
    private static function trail(array $items): array
    {
        $trail = [['name' => 'Ana Sayfa', 'url' => rtrim(route('anasayfa'), '/').'/']];

        foreach ($items as [$name, $url]) {
            $trail[] = ['name' => $name, 'url' => $url];
        }

        return $trail;
    }
}
