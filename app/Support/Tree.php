<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Kendine referans veren (parent_id) bir koleksiyonu ağaç sırasıyla
 * düzleştirir, her öğeye derinliğini ekler.
 *
 * <x-admin::form.select>'in hiyerarşik (girintili) render'ında kullanılır:
 *
 *   $options = Tree::options(ServiceRegion::orderBy('sort_order')->get());
 *   // [1 => ['label' => 'Gaziantep', 'depth' => 0], 82 => ['label' => 'Şahinbey', 'depth' => 1], ...]
 *
 * Öğeler zaten istenen sırada verilmelidir (örn. sort_order'a göre) — bu
 * sınıf yalnızca ebeveyn altına toplar, kendi başına sıralamaz. Kimliklerin
 * hep pozitif olduğu varsayılır (auto-increment); kök öğeler için 0 iç
 * sentinel olarak kullanılır, null/'' groupBy belirsizliğinden kaçınmak için.
 */
class Tree
{
    /**
     * @param  Collection<int, object{id: int, parent_id: ?int}>  $items
     * @param  int|null  $exceptId  Bu kimlik ve altındaki her şey hariç tutulur
     *                              — bir kaydı kendine ya da kendi altına
     *                              bağlamak döngü yaratır (taşıma senaryosu için).
     * @return array<int, array{label: string, depth: int}>
     */
    public static function options(Collection $items, string $labelKey = 'name', ?int $exceptId = null): array
    {
        $byParent = $items->groupBy(fn ($item) => $item->parent_id ?? 0);
        $result = [];

        $walk = function (int $parentId, int $depth) use (&$walk, $byParent, $labelKey, $exceptId, &$result): void {
            foreach ($byParent->get($parentId, collect()) as $item) {
                if ($item->id === $exceptId) {
                    continue;
                }

                $result[$item->id] = ['label' => $item->{$labelKey}, 'depth' => $depth];
                $walk($item->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $result;
    }

    /**
     * Bir ağaç seçeneğinin görüntülenecek metnini ve Choices.js'e geçecek
     * özel özelliklerini üretir. `options()`'ın ürettiği `['label' => ...,
     * 'depth' => ...]` çiftini hem `<x-admin::form.select>` bileşeni hem de
     * (bileşen kullanmayan) ham filtre select'leri aynı şekilde işlesin diye
     * paylaşılan tek yer burası.
     *
     * @return array{display: string, customProperties: array<string, mixed>}
     */
    public static function render(string $label, int $depth = 0, ?string $icon = null): array
    {
        // Choices henüz kurulmadan (ya da 'plain' modda) görünen ham metin:
        // her seviye görünmez boşluk + bir "alt dal" oku ile girintilenir.
        // Choices kurulunca core/select.js kendi girinti/ikonunu uygular,
        // bu metin ekranda görünmez hale gelir — yalnızca düşer.
        $display = $depth > 0
            ? str_repeat("\u{00A0}\u{00A0}\u{00A0}\u{00A0}", $depth)."\u{21B3} {$label}"
            : $label;

        return [
            'display' => $display,
            'customProperties' => array_filter(
                ['icon' => $icon, 'depth' => $depth ?: null],
                fn ($value) => $value !== null,
            ),
        ];
    }
}
