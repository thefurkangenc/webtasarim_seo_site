<?php

namespace App\Services\Admin;

use App\Contracts\ProvidesMenuBadge;
use Illuminate\Support\Facades\Auth;

/**
 * config/admin-menu.php'yi kullanıcının izinlerine göre süzer.
 * Sidebar Blade'i yalnızca çıktıyı render eder.
 */
class MenuService
{
    /** @return array<int, array<string, mixed>> */
    public function build(): array
    {
        $groups = [];

        foreach (config('admin-menu', []) as $group) {
            $items = $this->filter($group['items'] ?? []);

            if ($items !== []) {
                $groups[] = ['title' => $group['title'] ?? null, 'items' => $items];
            }
        }

        return $groups;
    }

    /**
     * Öğe ya da alt öğelerinden biri aktif route'a denk geliyor mu.
     *
     * @param  array<string, mixed>  $item
     */
    public function isActive(array $item): bool
    {
        $pattern = $item['active'] ?? $item['route'] ?? null;

        if ($pattern && request()->routeIs($pattern)) {
            return true;
        }

        foreach ($item['children'] ?? [] as $child) {
            if ($this->isActive($child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * İzni olmayan öğeleri atar; alt öğesi kalmayan üst öğeyi de kaldırır.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function filter(array $items): array
    {
        $allowed = [];

        foreach ($items as $item) {
            if (isset($item['permission']) && ! Auth::user()?->can($item['permission'])) {
                continue;
            }

            if (isset($item['children'])) {
                $item['children'] = $this->filter($item['children']);

                if ($item['children'] === []) {
                    continue;
                }
            }

            if (isset($item['badge'])) {
                $item['badge'] = $this->badge($item['badge']);
            }

            $allowed[] = $item;
        }

        return $allowed;
    }

    /**
     * Öğedeki `badge` bir sınıf adıdır (config cache'lenebilsin diye closure
     * değil). Sınıf ProvidesMenuBadge uygular ve rozet yoksa null döner.
     *
     * @param  class-string  $provider
     * @return array{count: int, status: string}|null
     */
    private function badge(string $provider): ?array
    {
        $instance = app($provider);

        return $instance instanceof ProvidesMenuBadge ? $instance->menuBadge() : null;
    }
}
