<?php

namespace App\Services\Notice;

use App\Models\Announcement\Announcement;
use App\Models\Blog\Blog;
use App\Models\Page\Page;
use App\Models\Popup\Popup;
use App\Models\Service\Service;
use App\Support\AudienceContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Bu istekte gösterilecek şerit ve popup. Her istekte bir kez çözülür
 * (layout.app'den çağrılır); eşleşen birden fazla kayıt varsa en yenisi kalır.
 */
class NoticeResolver
{
    private ?AudienceContext $context = null;

    public function forRequest(Request $request): array
    {
        $context = $this->context($request);

        return [
            'bar' => $this->firstMatch(Announcement::query()->live()->latest('id')->get(), $context)?->toPublic(),
            'popup' => $this->firstMatch(Popup::query()->live()->with('media')->latest('id')->get(), $context)?->toPublic(),
        ];
    }

    public function context(Request $request): AudienceContext
    {
        return $this->context ??= $this->resolve($request);
    }

    /** Formlardaki sayfa / yazı / hizmet seçenekleri. */
    public function options(): array
    {
        return [
            'audiences' => config('notices.audiences'),
            'pages' => Page::query()->orderBy('title')->pluck('title', 'id')->all(),
            'blogs' => Blog::query()->orderBy('title')->pluck('title', 'id')->all(),
            'services' => Service::query()->orderBy('title')->pluck('title', 'id')->all(),
        ];
    }

    private function resolve(Request $request): AudienceContext
    {
        $route = $request->route()?->getName();

        return new AudienceContext(
            isHome: $route === 'anasayfa',
            pageId: $route === 'sayfa.show'
                ? Page::query()->where('path', $request->route('path'))->value('id')
                : null,
            blogId: $route === 'blog.show'
                ? Blog::query()->where('slug', $request->route('slug'))->value('id')
                : null,
            serviceId: in_array($route, ['hizmetler.show', 'hizmetler.show-region'], true)
                ? Service::query()->where('slug', $request->route('slug'))->value('id')
                : null,
        );
    }

    /** @param  Collection<int, Announcement|Popup>  $records */
    private function firstMatch(Collection $records, AudienceContext $context): Announcement|Popup|null
    {
        return $records->first(fn ($record) => $record->matchesAudience($context));
    }
}
