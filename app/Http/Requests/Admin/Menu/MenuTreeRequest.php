<?php

namespace App\Http\Requests\Admin\Menu;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Sürükle-bırak sonrası tüm ağacın kaydı. Payload iç içe:
 *
 *   nodes: [ { id: 4, children: [ { id: 9, children: [] } ] }, ... ]
 *
 * Derinlik config('menus.max_depth') ile sınırlıdır; ön yüz teması daha
 * derin dropdown açmıyor.
 */
class MenuTreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('menu.update');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nodes' => ['present', 'array'],
            'nodes.*' => ['array', $this->depthRule()],
        ];
    }

    /**
     * Ağacın hiçbir dalı sınırdan derin olamaz. Recursive olarak iner;
     * her düğümde `id` bir tamsayı, `children` bir dizi olmalı.
     */
    private function depthRule(): Closure
    {
        $maxDepth = (int) config('menus.max_depth', 3);

        $walk = function (array $node, int $depth) use (&$walk, $maxDepth): bool {
            if (! isset($node['id']) || ! is_numeric($node['id'])) {
                return false;
            }

            if ($depth > $maxDepth) {
                return false;
            }

            foreach ($node['children'] ?? [] as $child) {
                if (! is_array($child) || ! $walk($child, $depth + 1)) {
                    return false;
                }
            }

            return true;
        };

        return function (string $attribute, mixed $value, Closure $fail) use ($walk, $maxDepth): void {
            if (! is_array($value) || ! $walk($value, 1)) {
                $fail("Menü en fazla {$maxDepth} seviye derinleşebilir.");
            }
        };
    }
}
