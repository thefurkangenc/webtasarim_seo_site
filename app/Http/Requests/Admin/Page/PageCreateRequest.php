<?php

namespace App\Http\Requests\Admin\Page;

use App\Http\Requests\Concerns\FiltersPermissionedFields;
use App\Http\Requests\Concerns\ValidatesSharedFields;
use App\Models\Page\Page;
use App\Services\Page\PageService;
use App\Support\ReservedPath;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageCreateRequest extends FormRequest
{
    use FiltersPermissionedFields, ValidatesSharedFields;

    public function authorize(): bool
    {
        return $this->user()->can('page.store');
    }

    /**
     * Paylaşılan bileşen alanları izinsiz kullanıcının validated() çıktısından
     * düşer (bkz. FiltersPermissionedFields). UI tarafı aynı izinlerle
     * `resources/views/admin/pages/page/form.blade.php`'de gizlenir.
     *
     * @return array<string, array<int, string>>
     */
    protected function permissionedFields(): array
    {
        return $this->sharedComponentPermissions('page', null);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],

            // Boş bırakılırsa başlıktan türetilir. Serbest metin kabul edilir
            // (Türkçe karakter dahil) — PageService kaydederken slug'a çevirir,
            // bu yüzden burada biçim kuralı yok. Kök segmentin kayıtlı bir
            // route'u gölgelemediği after() içinde denetlenir: slug boş
            // gönderildiğinde `nullable` sonraki kuralları atlar, oysa o durumda
            // da başlıktan türetilen adın denetlenmesi gerekiyor.
            'slug' => ['nullable', 'string', 'max:200'],

            'parent_id' => ['nullable', 'integer', 'exists:pages,id', $this->parentRule()],

            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],

            'template' => ['required', Rule::in(array_keys((array) config('pages.templates')))],
            'status' => ['required', Rule::in(array_keys(Page::STATUSES))],

            // Yayın tarihi ileri bir tarihse sayfa panelde "Yayında" görünür
            // ama ön yüzde o tarihe kadar çıkmaz (bkz. Page::scopeVisible).
            'published_at' => ['nullable', 'date'],

            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],

            'faqs' => ['nullable', 'array'],
            'faqs.*' => ['integer', 'exists:faqs,id'],

            ...$this->tagRules(),
            ...$this->seoRules(),
            ...$this->schemaRules(),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        // Genel dil dosyasında `parent_id` "üst klasör" (medya kütüphanesi);
        // sayfa bağlamında doğru karşılık bu.
        return ['parent_id' => 'üst sayfa'];
    }

    /**
     * Kök segment denetimi. Ayrı bir `after` kancası olmasının nedeni, slug
     * alanının boş bırakılabilmesi: `nullable` boş değerde sonraki kuralları
     * atladığı için kurala bağlı bir closure hiç çalışmaz, oysa o durumda da
     * başlıktan türetilecek ad denetlenmelidir.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                // Başlık ya da slug kendi kuralından düştüyse ikinci bir hata
                // eklemek kullanıcıyı yanıltır.
                if ($validator->errors()->hasAny(['title', 'slug'])) {
                    return;
                }

                // Alt sayfalarda kök segment üst sayfadan gelir ve o zaten bir
                // kez denetlenmiştir; yalnızca kök seviyedeki sayfalar sayılır.
                if (filled($this->input('parent_id'))) {
                    return;
                }

                $slug = Str::slug((string) ($this->input('slug') ?: $this->input('title')), '-', 'tr');

                // Catch-all en sonda olduğu için böyle bir sayfa siteyi bozmaz
                // ama **hiç açılmaz** — sessizce erişilemez kalmasın diye
                // doğrulamada reddedilir.
                if (ReservedPath::taken($slug)) {
                    $validator->errors()->add(
                        'slug',
                        "\"{$slug}\" adresi sitede başka bir sayfa tarafından kullanılıyor. Başka bir kısa ad seçin.",
                    );
                }
            },
        ];
    }

    /**
     * Üst sayfa üç şeyi ihlal edemez: sayfanın kendisi olamaz, sayfanın
     * altındaki bir sayfa olamaz (yol çözümlemesi döngüye girer) ve taşınan
     * dal derinlik sınırını aşamaz.
     */
    private function parentRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $parent = Page::find($value);

            if (! $parent) {
                return;
            }

            $page = $this->route('page');
            $maxDepth = (int) config('pages.max_depth', 3);

            if ($page instanceof Page) {
                if ($parent->is($page)) {
                    $fail('Bir sayfa kendisinin alt sayfası olamaz.');

                    return;
                }

                if (str_starts_with($parent->path, $page->path.'/')) {
                    $fail('Bir sayfa, kendi alt sayfalarından birinin altına taşınamaz.');

                    return;
                }
            }

            // Taşınan sayfanın altındaki en derin torun da sınırın içinde kalmalı.
            $height = $page instanceof Page ? app(PageService::class)->subtreeHeight($page) : 0;

            if ($parent->depth() + 1 + $height > $maxDepth - 1) {
                $fail($height > 0
                    ? "Bu sayfanın alt sayfaları var; buraya taşınırsa adres {$maxDepth} seviyeden derin olur."
                    : "Adresler en fazla {$maxDepth} seviye derinleşebilir. Daha üst bir sayfa seçin.");
            }
        };
    }
}
