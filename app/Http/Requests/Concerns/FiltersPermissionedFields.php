<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Arr;

/**
 * Paylaşılan bileşenlerin (SEO, Schema.org, Etiketler, Sınıflandırma, SSS)
 * alan bazlı yetkisi.
 *
 * Arayüz bu blokları izinsiz kullanıcıya zaten GÖSTERMİYOR (ilgili form
 * Blade'indeki `@can` blokları) — ama bu tek başına güvenlik değildir, aynı
 * istek ham bir HTTP çağrısıyla da gönderilebilir. Bu trait doğrulamadan
 * SONRA, servise ulaşmadan ÖNCE izni olmayan alanları `validated()` çıktısından
 * sessizce düşürür; arayüzü atlayan bir istek de kaydedilmez.
 *
 * Create ve Update Request'leri aynı kuralı paylaştığı için (Update, Create'i
 * extend eder) bu metot yalnızca Create sınıfında tanımlanır.
 *
 *   use FiltersPermissionedFields;
 *
 *   protected function permissionedFields(): array
 *   {
 *       return $this->sharedComponentPermissions('blog', 'blog_category_id');
 *   }
 */
trait FiltersPermissionedFields
{
    /** @return array<string, array<int, string>> */
    protected function permissionedFields(): array
    {
        return [];
    }

    /**
     * 4 modülün ortak kalıbı: seo/schema-org/tags/faqs her zaman
     * ValidatesSharedFields'teki aynı alan adlarını taşır. `classification`
     * modüle göre farklı bir alana karşılık geldiği için ayrıca verilir;
     * modülde sınıflandırma alanı yoksa (örn. Sayfa) null geçilir.
     *
     * @return array<string, array<int, string>>
     */
    protected function sharedComponentPermissions(string $module, ?string $classificationField = null): array
    {
        $map = [
            "{$module}.seo" => [
                'seo.meta_title', 'seo.meta_description', 'seo.meta_keywords',
                'seo.focus_keyword', 'seo.og_media_id',
            ],
            "{$module}.schema-org" => ['seo.schema_type', 'seo.schema_override', 'seo.schema_json'],
            "{$module}.tags" => ['tags'],
            "{$module}.faqs" => ['faqs'],
        ];

        if ($classificationField !== null) {
            $map["{$module}.classification"] = [$classificationField];
        }

        return $map;
    }

    /**
     * @param  string|null  $key
     * @param  mixed  $default
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        foreach ($this->permissionedFields() as $permission => $fields) {
            if ($this->user()->can($permission)) {
                continue;
            }

            foreach ($fields as $field) {
                Arr::forget($data, $field);
            }
        }

        return $key === null ? $data : data_get($data, $key, $default);
    }
}
