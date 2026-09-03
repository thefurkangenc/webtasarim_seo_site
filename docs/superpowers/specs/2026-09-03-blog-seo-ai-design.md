# Blog Modülü, Paylaşılan SEO/Etiket Bileşenleri ve Yapay Zeka İçerik Modülü

Tarih: 2026-09-03
Durum: Onaylandı, uygulanıyor

Bu belge Faz 0 (altyapı) ve medya fazının üzerine gelen ilk gerçek içerik
modülünü ve onunla birlikte kurulan iki paylaşılan bileşeni tanımlar.
Önceki karar kaydı: `2026-09-03-admin-panel-design.md`.

## Kapsam

1. Paylaşılan zemin — SEO bileşeni, etiket bileşeni, zengin metin editörü
2. Yapay zeka içerik modülü — sağlayıcılar, prompt şablonları, üretim kuyruğu
3. Blog kategori modülü
4. Blog modülü

Ön yüz (public) blog sayfaları bu turun kapsamı dışında; Service katmanı ortak
olduğu için ayrı bir turda eklenir.

## Kararlar

| Konu | Karar | Gerekçe |
|---|---|---|
| SEO saklama | Polymorphic `seo` tablosu + `HasSeo` trait | Medyadaki kalıbın aynısı. Yeni modüle SEO eklemek migration gerektirmez; alan eklendiğinde tek yerde değişir. |
| Etiket | `tags` + `taggables` polymorphic pivot | Etiket arşiv sayfası ve etiket sayımı mümkün olur; JSON kolonda bunlar yapılamaz. Pivot medyadaki `mediables` ile aynı şekilde çalışır. |
| Kategori | Ayrı `BlogCategory` modülü | Blog'a ait ama kendi ekranı olan bir varlık; blog formunda select olarak görünür. |
| Editör | TinyMCE 7.2.1, self-host | Kullanıcı kararı. Dosyalar `public/admin/assets/js/vendor/tinymce/` altına elle konuldu. GPL self-host, API anahtarı gerekmez, `tr.js` ve `oxide-dark` skin mevcut. Tablo desteği Quill'de yoktu. |
| AI istek modeli | Kuyruk + durum sorgulama | Kullanıcı kararı. Ollama yerelde dakikalarca sürebilir; senkron istekte PHP-FPM zaman aşımı riski var. |
| AI çıktısı | Tek JSON, tüm alanları doldurur | Tek istekte başlık + özet + içerik + etiket + meta üretilir; alan başına ayrı istek token ve bekleme israfı olurdu. |
| Prompt şablonu | İsimli şablonlar, `key` ile modüle bağlı | Aynı modül için birden çok üslup ("Kısa tanıtım", "Detaylı rehber") tanımlanabilir. |
| Blog formu | Tam sayfa (create/edit) | Editör + SEO + etiket + AI paneli modala sığmaz. Kategori/etiket/sağlayıcı/prompt ekranları modal kalır. |

## Paylaşılan Zemin

### SEO

Tablo `seo`: `seoable_type`, `seoable_id` (unique birlikte), `meta_title`,
`meta_description`, `meta_keywords`, `canonical_url`, `robots_index` (bool),
`robots_follow` (bool), `og_media_id` (nullable, `media` tablosuna FK).

```php
class Blog extends Model { use HasSeo; }
```

```blade
<x-admin::form.seo :model="$blog" />
```

Bileşen; meta açıklama için karakter sayacı, robots anahtarları ve **canlı
Google sonuç önizlemesi** içerir. Kaydetme `SeoService::sync($model, $data)`
üzerinden; her modülün servisi tek satırla çağırır.

Boş bırakılan `meta_title` / `meta_description` okurken modelin kendi
`title` / `excerpt` alanına düşer — `Seo::resolved($model)`.

### Etiket

Tablolar: `tags` (`name`, `slug`, `is_active`), `taggables` (`tag_id`,
`taggable_type`, `taggable_id`, unique birlikte).

```php
class Blog extends Model { use HasTags; }
```

```blade
<x-admin::form.tags :model="$blog" />
```

Alan; yazarken mevcut etiketleri önerir, olmayan etiketi Enter ile yeni olarak
oluşturur (`TagService::sync()` içinde `firstOrCreate`). Slug Türkçe karakter
duyarlı üretilir.

### Editör

`<x-admin::form.editor name="content" :value="$blog?->content" />`
→ `public/admin/assets/js/core/editor.js`

TinyMCE `no-jquery` şekilde, ES modülü içinden global `tinymce` üzerinden
başlatılır. Karanlık mod panelin temasını izler. Görsel ekleme butonu mevcut
medya seçicisini açar (`mediaPicker.open()`), TinyMCE'nin kendi yükleyicisi
kullanılmaz — böylece tüm görseller medya kütüphanesine düşer.

## Yapay Zeka Modülü

### Tablolar

`ai_providers` — `name`, `driver` (`openai|deepseek|ollama`), `base_url`,
`api_key` (encrypted cast), `model`, `temperature`, `max_tokens`, `timeout`,
`is_active`, `is_default`, `options` (json).

`ai_prompts` — `name`, `key` (örn. `blog.content`), `system_prompt`,
`user_prompt`, `ai_provider_id` (nullable → varsayılan sağlayıcı),
`is_default`, `is_active`.

`ai_generations` — `ai_prompt_id`, `ai_provider_id`, `user_id`, `status`
(`queued|running|completed|failed`), `input` (json), `output` (json),
`error`, `tokens`, `duration_ms`.

### Servis

```
App\Services\Ai\AiService                 dispatch(), status(), test()
App\Services\Ai\Contracts\ChatDriver      chat(array $messages, array $options): array
App\Services\Ai\Drivers\OpenAiCompatibleDriver   openai + deepseek
App\Services\Ai\Drivers\OllamaDriver             /api/chat, anahtar istemez
App\Jobs\Ai\GenerateContentJob
```

ChatGPT ve DeepSeek aynı `/v1/chat/completions` sözleşmesini konuştuğu için tek
sürücü; yalnızca `base_url` ve varsayılan model farkı. Ollama'nın kendi
`/api/chat` uç noktası kullanılır — OpenAI uyumlu katmanı her kurulumda açık
olmayabilir.

Model cevabı savunmacı ayrıştırılır: ```json çitleri temizlenir, ilk `{` ile
son `}` arası alınır, `json_decode` başarısızsa üretim `failed` olur ve ham
cevap `error` alanına yazılır — sessizce boş dönmez.

### Akış

1. Blog formunda "Yapay Zeka ile Oluştur" → modal: prompt şablonu, anahtar
   kelimeler, opsiyonel başlık, uzunluk.
2. `POST /admin/ai/generate` → `ai_generations` kaydı + `GenerateContentJob`
   kuyruğa → `{ id }`.
3. JS 2 saniyede bir `GET /admin/ai/generate/{id}` sorar.
4. `completed` → alanlar doldurulur (içerik editöre, etiketler etiket alanına,
   meta alanları SEO bileşenine).

Kuyruk işçisi çalışmıyorsa kayıt `queued` durumunda kalır. Arayüz 20 saniye
sonra "kuyruk işçisi çalışmıyor olabilir" uyarısı gösterir; sessiz bekleme yok.

## Blog

`blog_categories` — `name`, `slug`, `description`, `sort_order`, `is_active`.
SEO bağlanır.

`blogs` — `blog_category_id`, `user_id`, `title`, `slug`, `excerpt`, `content`,
`status` (`draft|published`), `published_at`, `is_featured`, `view_count`.
Kapak görseli `media` üzerinden `cover` koleksiyonunda, `blog.cover` preset'i
ile. Etiket ve SEO paylaşılan bileşenlerden.

Slug boş bırakılırsa başlıktan üretilir (`Str::slug($title, '-', 'tr')`),
çakışma varsa sonuna sayı eklenir.

## İzinler

`blog.*`, `blog-category.*`, `tag.*`, `ai.provider.*`, `ai.prompt.*`,
`ai.generate` — `config/permissions.php` içine eklenir, kategorileri
`categories` dizisine yazılır.

## Kapsam Dışı

- Ön yüz blog sayfaları
- Toplu (liste ile) AI üretimi — kuyruk altyapısı hazır bırakılır
- Streaming (canlı yazım) — sürücü arayüzü sonradan eklemeye açık
- AI ile görsel üretimi

---

## Uygulama Sonucu (2026-09-03)

Tasarımdan sapmalar ve yol boyunca çıkanlar.

### Tasarımdan sapmalar

| Tasarım | Uygulanan | Neden |
|---|---|---|
| `SeoService::sync()` | Servis yok; `HasSeo::syncSeo()` işi kendisi yapıyor | `updateOrCreate` beş satır. Ayrı bir servis sınıfı `HasMedia::syncMedia()` ile tutarsız olurdu. |
| Etiket yönetim ekranı | Yok; yalnızca `/admin/tags/search` öneri uç noktası | Etiketler modül formundan doğuyor, ayrı bir CRUD ekranı talep edilmedi. `tag.*` izinleri de eklenmedi. |
| `AiProviderRequest` (tek) | `Create` + `Update` (extends) | CLAUDE.md'deki Create/Update/Filter konvansiyonu. |

### Ek olarak çıkan parçalar

- `App\Support\Field` — bileşenlere alan adı nokta notasyonuyla verilir, HTML
  `name` köşeli paranteze çevrilir, `data-error` noktalı kalır. Bu olmadan
  `seo[meta_title]` alanının doğrulama hatası hiçbir yere basılmıyordu.
- `App\Support\Slug` — Türkçe karakter duyarlı, tabloda benzersiz slug.
- `App\Http\Requests\Concerns\ValidatesSharedFields` — `seoRules()`, `tagRules()`.
- `config/media.php` → `seo.og` preset'i (1200×630).

### Yol boyunca düzeltilen dört hata

1. **`HasSeo::syncSeo()` tanımsız anahtarda patlıyordu.** `$data['og_media_id'] ?: null`
   yazılmıştı; `?:` anahtar yokluğunu susturmaz. `($data[...] ?? null) ?: null` oldu.
2. **Aynı hata servislerde de vardı** — `slug`, `blog_category_id`, `published_at`,
   `ai_provider_id`. `FormRequest::validated()` gönderilmemiş nullable alanları
   diziye koymadığı için slug'sız her kategori/yazı kaydı 500 dönüyordu. Beş yerde
   düzeltildi ve `laravel-architecture` skill'ine kural olarak yazıldı.
3. **Blade bileşen özniteliğinde karmaşık ifade.** `:options="collect($drivers)->map(fn ($d) => $d['label'])->all()"`
   ve `{{ '{{keywords}}' }}` yazımları Blade'in öznitelik/echo ayrıştırıcısını
   bozup sayfayı 500'e düşürüyordu. İfadeler `@php` bloğuna alındı, literal
   süslü parantez için `@{{...}}` kullanıldı.
4. **Türkçeleşmemiş alan adları.** Doğrulama hataları "ai prompt id" gibi ham
   alan adlarıyla dönüyordu. `lang/tr/validation.php` içindeki `attributes`
   dizisine SEO, etiket ve yapay zeka alanları eklendi.

### Doğrulanan davranışlar

- Etiket tekilleştirme slug üzerinden: "Web Tasarım" / "web tasarım" tek kayıt.
- Yeniden `syncTags` mevcut etiketleri çoğaltmıyor; bağı kopan etiket kaydı
  silinmiyor (başka modüller kullanıyor olabilir).
- `syncSeo` tekrar çağrıldığında ikinci satır açmıyor.
- Türkçe slug: `Şeker Fabrikasında SEO` → `seker-fabrikasinda-seo`;
  çakışmada `web-tasarim-2`. JS ve PHP slug üreticileri aynı sonucu veriyor
  (önizlemedeki URL ile kaydedilen slug tutuyor).
- Sağlayıcı API anahtarı veritabanında şifreli duruyor; form boş gönderildiğinde
  kayıtlı anahtar korunuyor.
- Varsayılan sağlayıcı ve varsayılan şablon tekilliği korunuyor.
- Sürücü hataları okunabilir Türkçe mesaja dönüyor: geçersiz anahtar (401),
  bağlantı reddi, JSON dönmeyen model, sağlayıcı tanımsız.
- Kuyruk yolu uçtan uca: `dispatch` → `jobs` tablosu → `queue:work` → `completed`,
  çıktı JSON'u form alanlarına eşlenebilir şekilde.
- Yazısı olan kategori silinmiyor; boş kategori siliniyor.
- Yazı silindiğinde medya kütüphaneden silinmiyor, yalnızca bağ kopuyor.
- Derlenmiş Tailwind çıktısından hiçbir seçici kaybolmadı (yeni build'de
  kaybolan 0, eklenen 47).

### Bilinen sınırlar

- Kuyruk işçisi çalışmıyorsa üretim `queued` kalır. Arayüz 20 saniye sonra
  uyarıyor ama işi kendiliğinden çalıştırmıyor — bilinçli.
- Model çıktısı HTML olarak editöre giriyor; sunucuda HTML temizleme yapılmıyor.
  Üretimi yalnızca güvenilen kullanıcılar tetikleyebildiği için (izin `ai.generate`)
  şimdilik kabul edildi.
- Yazıyı taslağa çekerken form yayın tarihini göndermezse tarih siliniyor.
  Gerçek formda alan her zaman dolu gittiği için görünmüyor.
- Toplu üretim, streaming ve etiket yönetim ekranı yapılmadı.
