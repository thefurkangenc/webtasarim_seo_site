---
name: laravel-architecture
description: Use when writing or editing any PHP under app/ - controllers, services, form requests, models, routes, enums. Defines the thin-controller / fat-service contract, folder naming per module, JSON response format, permission checks and the "don't split every step into its own method" rule.
---

# Laravel Katman Mimarisi

## Katman sorumlulukları

```
Request  ->  Controller  ->  Service  ->  Model/DB
             (ince)          (iş kuralı)
```

| Katman | Yapar | Yapmaz |
|---|---|---|
| FormRequest | Doğrulama, yetki kontrolü, mesajlar | Veri dönüşümü dışında iş kuralı |
| Controller | Request al, servisi çağır, view/JSON dön | Eloquent, iş kuralı, try/catch, dallanma |
| Service | Eloquent, iş kuralı, transaction, medya | HTTP bilgisi (`request()`, `redirect()`, `session()`) |
| Model | İlişkiler, cast, scope, accessor | İş kuralı |

## Klasör türetme

Modül adı `Blog` verildiğinde:

| Şey | Değer |
|---|---|
| PHP namespace parçası | `Blog` (StudlyCase) |
| Klasör / route / view / js | `blog` (kebab-case) |
| Tablo | `blogs` (snake_case, çoğul) |
| İzin öneki | `blog` |

Çok kelimeli: `BlogCategory` -> `blog-category` -> `blog_categories` -> `blog-category.view`

```
app/Http/Controllers/Admin/Blog/BlogController.php
app/Http/Requests/Admin/Blog/BlogCreateRequest.php
app/Http/Requests/Admin/Blog/BlogUpdateRequest.php
app/Http/Requests/Admin/Blog/BlogFilterRequest.php
app/Services/Blog/BlogService.php     <- Admin/ segmenti YOK (ön yüz de kullanacak)
app/Models/Blog/Blog.php              <- Admin/ segmenti YOK
```

## Controller şablonu

```php
<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\BlogCreateRequest;
use App\Http\Requests\Admin\Blog\BlogFilterRequest;
use App\Http\Requests\Admin\Blog\BlogUpdateRequest;
use App\Models\Blog\Blog;
use App\Services\Blog\BlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BlogController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly BlogService $service) {}

    public function index(): View
    {
        return view('admin.pages.blog.index');
    }

    public function datatable(BlogFilterRequest $request): JsonResponse
    {
        return $this->success(data: $this->service->list($request->validated()));
    }

    public function form(?Blog $blog = null): View
    {
        return view('admin.pages.blog.modals.form', $this->service->formData($blog));
    }

    public function store(BlogCreateRequest $request): JsonResponse
    {
        $this->service->create($request->validated());

        return $this->success('Blog yazısı oluşturuldu.');
    }

    public function update(BlogUpdateRequest $request, Blog $blog): JsonResponse
    {
        $this->service->update($blog, $request->validated());

        return $this->success('Blog yazısı güncellendi.');
    }

    public function destroy(Blog $blog): JsonResponse
    {
        $this->service->delete($blog);

        return $this->success('Blog yazısı silindi.');
    }
}
```

Bir controller metodu **üç satırı** geçiyorsa fazlalık servise taşınır.

## Service şablonu

```php
<?php

namespace App\Services\Blog;

use App\Models\Blog\Blog;
use App\Services\Media\MediaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlogService
{
    public function __construct(private readonly MediaService $media) {}

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Blog::query()->with('category:id,name');

        $this->applyFilters($query, $filters);

        return $query
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Blog
    {
        $data['slug'] = Str::slug($data['title']);

        if (isset($data['image'])) {
            $data['image'] = $this->media->upload($data['image'], 'blog');
        }

        return Blog::create($data);
    }

    public function update(Blog $blog, array $data): Blog
    {
        $data['slug'] = Str::slug($data['title']);

        if (isset($data['image'])) {
            $data['image'] = $this->media->replace($data['image'], 'blog', $blog->image);
        }

        $blog->update($data);

        return $blog;
    }

    public function delete(Blog $blog): void
    {
        $this->media->delete($blog->image);

        $blog->delete();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
            ))
            ->when($filters['category_id'] ?? null, fn (Builder $q, int $id) => $q->where('category_id', $id))
            ->when(isset($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']));
    }
}
```

### Metot bölme kuralı

`applyFilters` ayrı bir metottur çünkü **uzun** ve `list()` içinde okunabilirliği bozar.

Slug üretimi ayrı metot **değildir** — tek satır. Aşağıdakiler yanlıştır:

```php
// YANLIŞ — gereksiz metot enflasyonu
private function generateSlug(string $title): string { return Str::slug($title); }
private function setStatus(array $data): array { ... }
private function findBlog(int $id): Blog { return Blog::findOrFail($id); }
```

Private metot açmak için iki geçerli sebep vardır:
1. Blok gerçekten uzun (kabaca 10+ satır) ve çağıran metodu okunmaz hale getiriyor.
2. İki veya daha fazla public metot aynı bloğu paylaşıyor.

Başka sebep yoksa kod çağıran metodun içinde kalır.

## FormRequest

Create ve Update **ayrı** sınıflardır — unique kuralları farklılaşır.
Filtre parametreleri de doğrulanır; controller'a ham `Request` girmez.

```php
<?php

namespace App\Http\Requests\Admin\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blog.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('blogs')->ignore($this->route('blog'))],
            'category_id' => ['required', 'integer', 'exists:blog_categories,id'],
            'content'     => ['required', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status'      => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required'       => 'Başlık zorunludur.',
            'category_id.required' => 'Kategori seçilmelidir.',
            'image.max'            => 'Görsel en fazla 4 MB olabilir.',
        ];
    }
}
```

Hata mesajları Türkçe yazılır — arayüzde doğrudan input altına basılır.

## Model

```php
<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'title', 'slug', 'excerpt', 'content', 'image', 'status', 'published_at'])]
class Blog extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }
}
```

Görsel alanı olan modeller `App\Models\Concerns\HasMedia` trait'ini kullanır;
modelde `image` kolonu açılmaz, bağlantı `mediables` pivotu üzerinden kurulur.

Bu proje Laravel 13'ün **attribute** stilini kullanır: `#[Fillable([...])]`,
gerektiğinde `#[Hidden([...])]` — `protected $fillable` property'si değil.
Mevcut `app/Models/User.php` bu stildedir, ondan sapma.
`$guarded = []` kullanılmaz. Cast'ler `casts()` metodu ile tanımlanır.

## JSON sözleşmesi

`App\Http\Controllers\Concerns\RespondsWithJson`:

```php
protected function success(?string $message = null, mixed $data = null, int $status = 200): JsonResponse
protected function error(string $message, int $status = 422): JsonResponse
```

`success()` bir `LengthAwarePaginator` aldığında kayıtları `data`, sayfalama
bilgisini `meta` altına ayırır — `core/table.js` tam olarak bu yapıyı bekler:

```
{ "success": true, "message": null,
  "data": [...],
  "meta": { "current_page": 1, "last_page": 4, "per_page": 15, "total": 52, "from": 1, "to": 15 } }
```

```
200  { "success": true,  "message": "...", "data": {...} }
422  { "success": false, "message": "..." }                 iş kuralı hatası
422  { "message": "...", "errors": { "title": ["..."] } }   validation (Laravel default)
403  { "success": false, "message": "Bu işlem için yetkiniz yok." }
```

## Hata yönetimi

Servis iş kuralı ihlalinde exception fırlatır; controller yakalamaz:

```php
// Service içinde
if ($blog->comments()->exists()) {
    throw new DomainException('Yorumu olan yazı silinemez.');
}
```

`bootstrap/app.php` içindeki handler bunu `{success: false, message}` JSON'una çevirir.
Controller'da `try/catch` yazma.

## Yetki

İzinler **`config/permissions.php`** içinde tanımlanır; seeder oradan okur.

```php
['name' => 'blog.view', 'label' => 'Blog - Listele', 'category' => 'blog', 'guard_name' => 'web'],
```

Kategori etiketleri aynı dosyadaki `categories` dizisinde, rollerin izinleri
`roles` dizisinde desen olarak (`'editor' => ['blog.*']`) tutulur.
İzin adı serbesttir; önerilen kalıp `<modül>.<eylem>` ya da
`<üst>.<modül>.<eylem>` (`company.employee.index`).

- Yazma işlemleri: ilgili FormRequest'in `authorize()` metodunda.
- Okuma işlemleri: route üzerinde `->middleware('permission:blog.view')`.
- Blade: `@can('blog.create')`.
- `super-admin` rolü `AppServiceProvider`'daki `Gate::before` ile her izne sahiptir.

## Doğrulama metinleri

`lang/tr/validation.php` tam çeviridir; genel kurallar için ayrıca mesaj
yazmana gerek yok. `attributes` dizisi alan adlarını Türkçeleştirir.
FormRequest'in `messages()` metodunu yalnızca **alana özel** bir ifade
gerektiğinde kullan ("Yüklenecek dosyayı seçin." gibi).

## Route

`routes/admin.php` içinde, modül başına bir blok:

```php
Route::prefix('blog')->name('blog.')->controller(BlogController::class)->group(function () {
    Route::get('/', 'index')->name('index')->middleware('permission:blog.view');
    Route::get('/datatable', 'datatable')->name('datatable')->middleware('permission:blog.view');
    Route::get('/form/{blog?}', 'form')->name('form')->middleware('permission:blog.view');
    Route::post('/', 'store')->name('store');
    Route::put('/{blog}', 'update')->name('update');
    Route::delete('/{blog}', 'destroy')->name('destroy');
});
```

Grup zaten `admin` prefix + `admin.` name altındadır (`bootstrap/app.php`),
tam route adı `admin.blog.index` olur.

## Kontrol listesi

Kod yazmayı bitirince:

- [ ] Controller metotlarının hiçbiri 3 satırı geçmiyor
- [ ] Controller'da Eloquent, `if`, `try/catch` yok
- [ ] Servis private metotları "uzun ya da paylaşılan" testini geçiyor
- [ ] Create ve Update ayrı Request sınıflarında
- [ ] Validation mesajları Türkçe
- [ ] Servis içinde `request()`, `session()`, `redirect()` geçmiyor
- [ ] Model `Admin/` namespace'i altında değil
