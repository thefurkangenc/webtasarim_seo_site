<?php

namespace App\Services\Search;

use App\Models\User;
use App\Support\ModuleRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Header'daki global arama.
 *
 * Arama ucu yetki ara katmanından MUAFTIR (her oturum sahibi arayabilir),
 * bu yüzden yetki filtresi burada uygulanır: kullanıcının iznine sahip
 * olmadığı bir kaynak hiç sorgulanmaz ve sonucu sızmaz.
 *
 * Kaynak listesi config/global-search.php'de; yeni bir modül aranabilir olsun
 * istiyorsa oraya bir satır yazmak yeterli, burada kod değişmez.
 */
class GlobalSearchService
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    /**
     * @return array{term: string, total: int, groups: list<array<string, mixed>>}
     */
    public function search(string $term, User $user): array
    {
        $term = trim($term);

        if (mb_strlen($term) < (int) config('global-search.min_length', 2)) {
            return ['term' => $term, 'total' => 0, 'groups' => []];
        }

        $groups = [];

        foreach ($this->screens($term, $user) as $group) {
            $groups[] = $group;
        }

        foreach (config('global-search.sources', []) as $key => $source) {
            if (! $this->allows($user, $source['permission'] ?? null)) {
                continue;
            }

            if (! $this->modules->isActive($key)) {
                continue;
            }

            $items = $this->query($source, $term);

            if ($items !== []) {
                $groups[] = [
                    'key' => $key,
                    'label' => $source['label'],
                    'icon' => $source['icon'],
                    'items' => $items,
                ];
            }
        }

        return [
            'term' => $term,
            'total' => array_sum(array_map(fn (array $group) => count($group['items']), $groups)),
            'groups' => $groups,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @return list<array<string, mixed>>
     */
    private function query(array $source, string $term): array
    {
        /** @var class-string<Model> $model */
        $model = $source['model'];
        $columns = $source['columns'];

        return $model::query()
            ->where(function (Builder $query) use ($columns, $term) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%{$term}%");
                }
            })
            ->latest()
            ->limit((int) config('global-search.limit', 5))
            ->get()
            ->map(fn (Model $record) => [
                'title' => (string) ($record->{$source['title']} ?? '—'),
                'subtitle' => $source['subtitle'] ? (string) ($record->{$source['subtitle']} ?? '') : '',
                'url' => $this->url($source, $record),
            ])
            ->all();
    }

    /**
     * Kayda giden adres. Kendi düzenleme ekranı olmayan modüller (modal ile
     * yönetilenler: SSS, yorumlar, medya) liste ekranına gider — oraya
     * bir kayıt kimliğiyle derin bağlantı kurmak mümkün değil.
     *
     * @param  array<string, mixed>  $source
     */
    private function url(array $source, Model $record): string
    {
        return ($source['route'] ?? null)
            ? route($source['route'], $record->getKey())
            : route($source['index_route']);
    }

    /**
     * Panel ekranları — veritabanında olmayan hedefler. Etiket ya da anahtar
     * kelimelerden biri eşleşirse listelenir ("çerez" -> Site Ayarları).
     *
     * @return list<array<string, mixed>>
     */
    private function screens(string $term, User $user): array
    {
        $needle = mb_strtolower($term, 'UTF-8');

        $items = collect(config('global-search.screens', []))
            ->map(fn (array $screen) => $screen + ['url' => route($screen['route'])])
            ->concat($this->settingScreens())
            ->filter(fn (array $screen) => $this->allows($user, $screen['permission'] ?? null))
            ->filter(fn (array $screen) => str_contains(
                mb_strtolower($screen['label'].' '.($screen['keywords'] ?? ''), 'UTF-8'),
                $needle,
            ))
            ->map(fn (array $screen) => [
                'title' => $screen['label'],
                'subtitle' => $screen['subtitle'] ?? '',
                'url' => $screen['url'],
            ])
            ->values()
            ->all();

        return $items === []
            ? []
            : [[
                'key' => 'screens',
                'label' => 'Panel Ekranları',
                'icon' => 'dashboard',
                'items' => $items,
            ]];
    }

    /**
     * Ayar sekmeleri. Elle yazılmaz: `config/settings.php` > groups zaten her
     * sekmenin başlığını ve açıklamasını taşıyor, arama onları okur. Böylece
     * yeni bir ayar sekmesi eklendiğinde aranabilir olması için burada hiçbir
     * şey değişmez — "çerez" yazan biri Çerez Çubuğu sekmesine ulaşır.
     *
     * @return list<array<string, mixed>>
     */
    private function settingScreens(): array
    {
        return collect(config('settings.groups', []))
            ->map(fn (array $group, string $key) => [
                'label' => 'Ayarlar → '.$group['title'],
                'keywords' => $group['title'].' '.($group['description'] ?? '').' ayar',
                'subtitle' => $group['description'] ?? '',
                'url' => route('admin.setting.edit', $key),
                'permission' => 'setting.index',
            ])
            ->values()
            ->all();
    }

    /** İzni null olan kaynak herkese açıktır (örn. kendi profili). */
    private function allows(User $user, ?string $permission): bool
    {
        return $permission === null || $user->hasRole('super-admin') || $user->can($permission);
    }
}
