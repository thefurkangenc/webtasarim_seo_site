---
name: trezo-ui
description: Use when writing any admin Blade view or markup under resources/views/admin/ - page shell, cards, tables, forms, inputs, buttons, badges, modals, breadcrumb, pagination, sidebar. Maps each UI component to its reference file in the Trezo template and gives the exact class strings, so markup is copied from the template instead of invented.
---

# Trezo Admin UI

Admin arayüzü **Trezo** Tailwind CSS v4 template'i üzerine kuruludur.
Template kaynağı: `resources/views/admin/html/` — 219 sayfa, her biri 2000-3000 satır.

## Temel kural

**Class uydurma.** Bir bileşen yazmadan önce template'teki karşılığını bul ve
markup'ı oradan al. Template'te olmayan bir görsel ihtiyaç doğduğunda mevcut
kalıpları birleştir; yepyeni bir tasarım dili üretme.

Şüphelendiğin bir class'ı doğrula:

```sh
grep -c 'first\\:rounded-tl-md' public/admin/assets/css/style.css   # derlenmiş mi
grep -rl 'text-danger-500' resources/views/admin/html/               # template kullanıyor mu
```

Örnek: `text-danger-500` derlenmiştir ve 204 template sayfasında geçer;
`!border-danger-500` ise **derlenmemiştir** — Tailwind build kurulmadan
yazarsan sessizce hiçbir şey yapmaz.

Doğru referans dosyayı bulmak için aşağıdaki haritayı kullan. Dosyalar çok
büyük olduğundan **`trezo-ui-extractor` agent'ına** sor — ana bağlamı şişirme.

## Bileşen -> referans dosya haritası

| İhtiyaç | Dosya |
|---|---|
| Sayfa iskeleti, breadcrumb | `tables.html`, `pagination.html` (ilk 30 satır `main-content`'ten sonra) |
| Veri tablosu, sıralanabilir başlık | `tables.html` |
| Sayfalama | `pagination.html` |
| Form alanları (input/select/textarea) | `create-product.html`, `create-project.html`, `add-user.html` |
| Gelişmiş input, checkbox, radio, switch | `input-select.html` |
| Buton varyantları | `buttons.html` |
| Badge / durum etiketi | `badges.html` |
| Uyarı kutuları | `alerts.html` |
| Modal | `modal.html` |
| Sekme | `tabs.html` |
| Dropdown | `dropdowns.html` |
| Bildirim | `notifications.html` |
| Zengin metin editörü (Quill) | `rich-text-editor.html` |
| Dosya yükleme alanı | `create-product.html` |
| Giriş ekranı | `sign-in.html` |
| Boş / hata durumu | `error.html` |
| İkon listesi | `remixicon.html` |

## Sayfa iskeleti

```blade
@extends('admin.layout.app')
@section('admin.title', 'Blog Yazıları')

@section('content')
    {{-- Breadcrumb --}}
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Blog Yazıları</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}" class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Blog Yazıları
            </li>
        </ol>
    </div>

    {{-- İçerik kartı --}}
    <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
        <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex sm:items-center sm:justify-between">
            <div class="trezo-card-title">
                <h5 class="!mb-0">Liste</h5>
            </div>
            <div class="trezo-card-subtitle mt-[15px] sm:mt-0">
                {{-- arama, filtre, "Yeni Ekle" butonu --}}
            </div>
        </div>
        <div class="trezo-card-content">
            {{-- tablo --}}
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/blog/index.js') }}"></script>
@endpush
```

Layout `resources/views/admin/layout/app.blade.php`; stack'ler `admin.css` ve `admin.scripts`.

## Kart yapısı

Her içerik bloğu `trezo-card` içine girer:

```
trezo-card            > kartın kendisi
  trezo-card-header   > başlık satırı
    trezo-card-title  > sol taraf (h5)
    trezo-card-subtitle > sağ taraf (aksiyonlar)
  trezo-card-content  > gövde
  trezo-card-footer   > alt aksiyon çubuğu (opsiyonel)
```

Kart kabuğu class'ı sabittir:
`trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md`

## Tablo

```blade
<div class="table-responsive overflow-x-auto">
    <table class="w-full">
        <thead class="text-black dark:text-white">
            <tr>
                <th class="font-medium ltr:text-left rtl:text-right px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] whitespace-nowrap first:rounded-tl-md last:rounded-tr-md cursor-pointer relative" data-column="title">
                    Başlık
                    <i class="ri-expand-up-down-fill text-gray-500 dark:text-gray-400"></i>
                </th>
            </tr>
        </thead>
        <tbody class="text-black dark:text-white" id="blog-table-body"></tbody>
    </table>
</div>
```

- Sıralanabilir başlıklara `data-column="<kolon>"` konur; `core/table.js` bunu dinler.
- `<tbody>` boş bırakılır, satırlar JS ile basılır.
- İlk `<th>`'ye `first:rounded-tl-md`, son `<th>`'ye `last:rounded-tr-md`.

### Sürükle-bırak sıralama

Bir modülün liste sırası kullanıcı tarafından elle belirlenecekse (`sort_order`
kolonu var, formda **girilmez**) bu kalıp kullanılır — blog kategori bunun
referans uygulamasıdır.

**Model:** `use HasSortOrder;` (`App\Models\Concerns\HasSortOrder`) — yeni
kayıt otomatik `max(sort_order)+1` alır. Kapsamlı sıralama gerekiyorsa
(örn. klasör içi) `sortOrderScope()` ezilir.

**Servis:** `use ReordersRecords;` (`App\Services\Concerns\ReordersRecords`)
+ `protected function reorderModel(): string { return Model::class; }`.
`attributes()` metodunda `sort_order` **hiç geçmez** — formdan gelmiyor,
dokunulmazsa update sıfırlamaz.

**Request:** modül başına ayrı Request açılmaz, hepsi
`App\Http\Requests\Admin\ReorderRequest`'i kullanır (`ids: int[]`).

**Route:** sabit `reorder` segmenti, `{model}` joker'ından **önce**
tanımlanır — aksi halde "reorder" bir kimlik sanılır:

```php
Route::put('reorder', 'reorder')->name('reorder')->middleware('permission:blog-category.update');
Route::put('{category}', 'update')->name('update')->middleware('permission:blog-category.update');
```

**Controller:**

```php
public function reorder(ReorderRequest $request): JsonResponse
{
    $this->service->reorder($request->validated('ids'));
    return $this->success('Sıralama güncellendi.');
}
```

**Blade — tabloya iki şey eklenir:**

1. `<thead>`'de en solda gizli bir tutamaç kolonu:
   ```blade
   <th data-reorder-column class="hidden font-medium px-[20px] py-[11px] bg-gray-50 dark:bg-[#15203c] w-[36px] first:rounded-tl-md"></th>
   ```
2. Butonlar satırına bir "Sıralama Modu" butonu (`is-outline` stil, sadece
   `update` iznine sahip kullanıcıya):
   ```blade
   <button type="button" id="category-reorder" class="inline-flex items-center gap-[6px] py-[9px] px-[18px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
       <i class="material-symbols-outlined !text-[19px]">drag_indicator</i> Sıralama Modu
   </button>
   ```

**Sayfa JS — `DataTable`'a `reorder` verilir, satıra `data-id` ve
`reorderHandle()` eklenir:**

```js
import { cell, DataTable, reorderHandle } from '../../core/table.js';

const table = new DataTable({
    endpoint: '/admin/blog-category/datatable',
    body: document.getElementById('category-table-body'),
    reorder: {
        button: document.getElementById('category-reorder'),
        endpoint: '/admin/blog-category/reorder',
    },
    row: (item) => `<tr data-id="${item.id}">
        ${reorderHandle()}
        ${cell(...)}
    </tr>`,
});
```

Geri kalanı `core/table.js` hallediyor: buton tıklanınca tüm kayıtları
(sayfalamadan) `sort_order`'a göre çeker, arama/filtre/sayfalama/sütun
sıralamasını devre dışı bırakır, tutamaç kolonunu gösterir, SortableJS'i
tembel yükler ve her bırakışta `reorder` uç noktasına tek istek atar. Yeni
bir modülde sürükle-bırak istendiğinde **yalnızca yukarıdaki 6 parça**
eklenir — `core/table.js`'e dokunulmaz.

## Form alanları

**Standart boy `h-[42px]`, `text-sm`, `px-[14px]`** — Trezo template'inin kendi
`h-[55px]` boyutu bilinçli olarak küçültüldü (panel çok kalabalık görünüyordu).
Yeni bir alan tipi eklerken bu boyutu kullan, `h-[55px]`'e dönme.

Tek alanlık kalıp:

```blade
<div class="mb-[20px] md:mb-[25px] last:mb-0">
    <label class="mb-[10px] text-black dark:text-white font-medium block">
        Başlık
    </label>
    <input type="text" name="title"
        class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[14px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500"
        placeholder="Örn. Yeni web sitemiz yayında">
    <span class="text-danger-500 text-xs mt-[6px] block" data-error="title"></span>
</div>
```

Uzunluk farkları:

| Eleman | Farklı olan |
|---|---|
| `<textarea>` | `h-[120px]` ve `px-[14px]` yerine `p-[12px]` |
| `<select>` | `px-[12px]` ve `cursor-pointer`, `placeholder:*` yok, `data-choices` |

`data-error="<alan_adı>"` içeren `<span>` her alanın altına konur —
`core/form.js` 422 yanıtındaki mesajı buraya basar.

### Blade component'leri

Yukarıdaki class dizileri uzun ve her formda tekrar eder. Bunun yerine
`resources/views/admin/components/` altındaki anonim component'leri kullan:

```blade
<x-admin::form.input  name="title" label="Başlık" placeholder="Örn. ..." :value="$blog?->title" />
<x-admin::form.select name="category_id" label="Kategori" :options="$categories" :value="$blog?->category_id" />
<x-admin::form.textarea name="excerpt" label="Özet" />
<x-admin::form.switch name="status" label="Yayında" :checked="$blog?->status ?? true" />
<x-admin::form.actions submit="Kaydet" />

{{-- Görsel: preset kırpma oranını ve çıktı boyutunu belirler --}}
<x-admin::form.image name="cover_media_id" label="Kapak Görseli"
                     preset="blog.cover" :media="$blog?->getFirstMedia('cover')" />
```

`form.image` alanı diğerlerinden farklı çalışır: seçilen görseli **hemen**
yükler ve gizli input'a `media_id` yazar. Boyutlar `config/media.php`
içindeki `presets`'ten gelir; yeni bir alan için önce oraya boyut ekle.
Modelde `image` kolonu açma — bağlantı `HasMedia` trait'i ile kurulur.

Yerleşim: üstte `data-media-drop` çerçevesi — hem önizleme hem sürükle-bırak
alanı, boşken "sürükleyip bırakın ya da tıklayın" yazan tıklanabilir bir
placeholder. Kaldır butonu bu çerçevenin **sağ üst köşesinde**, tek başına,
sadece ikon, kırmızı arka plan (`bg-danger-500`) — buton sırasına karışmaz,
sadece görsel varken görünür. Altında `data-media-actions` bir `grid`: görsel
yokken 2 eşit sütun (Dosya Seç, Kütüphaneden Seç), görsel varken 3 eşit sütun
(Değiştir, Kütüphaneden Seç, Yeniden Kırp — sonuncusu preset yoksa gizli
kalır, o zaman yine 2 sütun). Sütun sayısı hem sunucu render'ında
(`$showRecrop` ile `grid-cols-2`/`grid-cols-3`) hem `media-field.js`'in
`render()`'ında aynı mantıkla belirlenir — biri değişirse öbürü de
güncellenmeli.

Component'ler `AppServiceProvider` içinde `Blade::anonymousComponentPath()` ile
`admin` namespace'ine bağlanır. Class dizileri **sadece** component dosyalarında
bulunur; sayfa Blade'lerinde ham input yazma.

Component'te olmayan bir alan tipi gerektiğinde önce component'i ekle.

### Tuzak: `hidden` + `inline-flex`/`inline`/`inline-block` birlikte kullanılmaz

Derlenmiş CSS'te `display` utility'leri şu sırayla çıkıyor: `block`, `flex`,
`grid`, `hidden`, `inline`, `inline-block`, `inline-flex`. Aynı specificity'ye
sahip iki kuralda **kaynak sırası** kazanır — `hidden`, `flex`/`grid`/`block`
class'ından SONRA geldiği için onları doğru şekilde ezer, ama `inline`/
`inline-block`/`inline-flex`'ten ÖNCE geldiği için onlar tarafından ezilir.
Yani `class="{{ $x ? '' : 'hidden' }} inline-flex ..."` şartı false olsa bile
`hidden` etkisiz kalır, eleman **görünür durur** — bu proje `form.image`
içindeki Kaldır/Yeniden Kırp butonlarında tam olarak bu hatayı yaşadı (buton
her zaman görünüyordu, hatta `grid-cols-2` bir ızgarada 3. sıraya taşıp
alt satıra düşüyordu). Kural: koşullu `hidden` ile birleşecek bir buton/eleman
`flex` veya `grid` kullanmalı, `inline-flex`/`inline`/`inline-block`
kullanmamalı. Kaçınılmazsa `!hidden` (important) ile zorla.

### Select — Choices.js

`<x-admin::form.select>` varsayılan olarak `data-choices` özniteliği taşır;
`core/select.js` bunu Choices.js'e çevirir (arama, tema uyumlu açılır liste).
Native `<select>` DOM'da kalır — `.value`, `change` olayı, FormData hiç
değişmez. Native tarayıcı select'i istiyorsan `plain` ver:

```blade
<x-admin::form.select name="status" label="Durum" :options="..." plain />
```

Sayfa Blade'inde raw `<select>` kullanıyorsan (tablo üstü filtre gibi) aynı
özniteliği elle ekle:

```blade
<select id="blog-status" data-choices class="...">
```

Boş `<option value="">Tüm durumlar</option>` gibi bir "hepsi" seçeneği normal
bir seçenek olarak kalır — Choices'in özel `placeholder` mekanizması
**kullanılmıyor**, çünkü bu seçenek gerçekten seçilebilir bir değer (tekrar
seçilebilmesi gerekiyor).

**İkonlu seçenekler:** `options`'a `value => label` yerine
`value => ['label' => ..., 'icon' => '/path/icon.svg']` verilebilir —
`form.select` bunu `<option data-custom-properties>` olarak yazar,
`core/select.js`'teki `withIcons()` şablonu Choices'in varsayılan
item/choice render'ının (aria/erişilebilirlik öznitelikleri korunarak)
başına `<img>` ekler. Sadece ikonu olan seçenekler etkilenir, karışık
listelerde sorun çıkarmaz. Örnek: `ai-provider/modals/form.blade.php`'teki
servis seçimi (ChatGPT/DeepSeek/Ollama ikonları).

### Tarih — Flatpickr

```blade
<x-admin::form.date name="published_at" label="Yayın Tarihi" :value="$blog?->published_at" />
{{-- yalnızca tarih, saat olmadan: --}}
<x-admin::form.date name="event_date" label="Etkinlik Tarihi" :time="false" />
```

Türkçe, 24 saat, `altInput` ile kullanıcıya `d.m.Y H:i` gösterilir; forma giden
gerçek değer `Y-m-d H:i` — Laravel'in `date` kuralı bunu doğrudan anlar,
Request'te ekstra format dönüşümü gerekmez.

### Modal içeriğinde otomatik kurulum

`AjaxModal.open()` içerik geldikten sonra `admin:content-loaded` olayını
yayar. `core/select.js`, `core/datepicker.js`, `core/seo-field.js`,
`core/tag-input.js` bunu dinler — modal içindeki select/tarih/SEO/etiket
alanları **sayfa JS'i hiçbir şey çağırmadan** kendiliğinden kurulur.

### Paylaşılan bileşenler

Üç bileşen modüle özel değildir; tek satırla çağrılır ve modele bağlanır.
Bunları modül Blade'ine kopyalayarak çoğaltma.

```blade
{{-- SEO: meta başlık/açıklama/anahtar kelime + paylaşım görseli + canlı Google önizlemesi --}}
<x-admin::form.seo :model="$blog" path="blog" imageSource="cover_media_id" />

{{-- Etiket: yazarken önerir, olmayanı Enter ile oluşturur --}}
<x-admin::form.tags :model="$blog" />

{{-- Zengin metin: TinyMCE 7, üst menü açık, görsel butonu medya seçicisini açar --}}
<x-admin::form.editor name="content" :value="$blog?->content" :height="560" />
```

`form.seo` içinde **canonical URL ve robots (index/follow) switch'leri yok** —
kaldırıldı, `HasSeo::syncSeo()` bunları formdan bağımsız olarak her zaman
`index,follow` ve canonical=null varsayar.

**Kaynak alan eşlemesi:** `titleSource` / `descriptionSource` / `slugSource` /
`imageSource` props'ları formdaki hangi alanın SEO'yu beslediğini söyler.
Alan adları modül tablosuna göre değişir, bu yüzden her modülde eşleşme
tekrar verilir — sabit varsayılan `title`/`excerpt`/`slug`'dır, `imageSource`
varsayılan olarak boştur (kapak görseli yoksa verilmez):

```blade
<x-admin::form.seo :model="$category" titleSource="name" descriptionSource="description"
                   path="blog/kategori" />
```

**Eşzamanlı doldurma (`core/seo-field.js`):** meta başlık/açıklama/paylaşım
görseli, kaynak alan değiştikçe **kullanıcı o meta alana daha önce hiç
dokunmadıysa** otomatik doldurulur — blog başlığına yazınca meta başlık da
aynı anda dolar. Kullanıcı meta alanı elle değiştirdiği (ya da düzenleme
ekranında zaten doluysa) andan itibaren senkron o alan için durur, bir daha
ezmez. Görsel senkronu `media:change` olayını dinler — `core/media-field.js`
bunu her yükleme/seçim/kaldırmada fırlatır, ayrı bir kablolamaya gerek yoktur.

Modelde `use HasSeo;` / `use HasTags;` olmadan bu bileşenler çalışmaz.

### Görsel alanı önizlemesi: `medium` kullan, `thumb` değil

`Media::toPayload()` iki küçültülmüş sürüm döndürür:

| Anahtar | Ne yapar | Nerede kullanılır |
|---|---|---|
| `thumb` | 400×400'e **cover-crop** — oranı bozar | Yalnızca medya kütüphanesi ızgarası (kart görünümü tek tip kare ister) |
| `medium` | Oranı koruyarak küçültür, kırpmaz | `<x-admin::form.image>` alan önizlemesi |

`thumb`'ı bir alan önizlemesinde kullanma: preset geniş/dar bir orana
sahipse (örn. `blog.cover` 1200×630) `thumb`'ın kendi kare kırpması
üste biner, kullanıcının modalda seçtiğinden çok daha fazla kırpılmış
görünür. Bu proje daha önce bu hatayı yaşadı — `image.blade.php` ve
`media-field.js`'in `render()`'ı her ikisi de `medium` kullanır, yeni bir
görsel önizlemesi eklerken aynısını yap.

**Yeniden kırpma her zaman `originalUrl()`/`original` üzerinden çalışır**,
`url()`/`medium` üzerinden DEĞİL — `path` önceki kırpımın sonucudur, kaynak
olarak kullanılırsa her seferinde biraz daha fazla kırpar (ya da alan hiç
`data-original` set etmemişse `<img>`'in `data-original` attribute'u boş
kalır, `fetch('')` sayfanın kendi HTML'ini çeker, kırpma modalı siyah/boş bir
canvas ile açılır — bu proje tam olarak bu hatayı yaşadı). Kural: `<img
data-media-image>`'in `data-original`'ı hem sunucu render'ında
(`$media?->originalUrl()`) hem `media-field.js`'in her `render()`
çağrısında (`media.original`) set edilir; ikisi de aynı kalmalı.

### Nokta notasyonlu alan adı

İç içe alanlarda ada nokta konur; bileşen HTML `name`'ini köşeli paranteze
çevirir, hata yuvası noktalı kalır (Laravel hataları o anahtarla döndürüyor):

```blade
<x-admin::form.input name="seo.meta_title" label="Meta Başlık" />
{{-- name="seo[meta_title]"  id="seo-meta_title"  data-error="seo.meta_title" --}}
```

Dönüşümü `App\Support\Field` yapar.

### Bileşen özniteliğinde karmaşık ifade yazma

Blade'in öznitelik ayrıştırıcısı `:options="collect($x)->map(fn ($d) => $d['label'])->all()"`
gibi içinde dizi erişimi olan ifadelerde bozulur ve sayfa 500 döner. İfadeyi
`@php` bloğunda hazırla, özniteliğe değişken ver:

```blade
@php($driverOptions = collect($drivers)->map(fn ($driver) => $driver['label'])->all())
<x-admin::form.select name="driver" :options="$driverOptions" />
```

Blade içinde literal `{{degisken}}` yazmak için `@{{degisken}}` kullan;
`{{ '{{degisken}}' }}` yazımı derleyiciyi bozar.

## Butonlar

```blade
{{-- birincil --}}
<button type="button" class="inline-block py-[10px] px-[30px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
    Kaydet
</button>

{{-- tehlike --}}
<button type="button" class="inline-block py-[10px] px-[30px] bg-danger-500 text-white transition-all hover:bg-danger-400 rounded-md border border-danger-500 hover:border-danger-400">
    Sil
</button>
```

Renk ailesini değiştirmek yeterli: `primary`, `secondary`, `success`, `danger`,
`warning`, `info`, `purple`, `orange`.

## Badge / durum etiketi

```blade
<span class="text-[10px] font-medium py-[1px] px-[8px] text-success-600 bg-success-100 dark:bg-[#ffffff14] inline-block rounded-sm">
    Yayında
</span>
```

Pasif için `text-danger-500 bg-danger-100`, taslak için `text-orange-500 bg-orange-100`.

## Modal

Template'in modal mekanizması: `.add-new-popup` elemanına `.active` class'ı
eklenince açılır — geçiş `style.scss` içinde tanımlıdır (`opacity` + `visibility`
+ `.popup-dialog` translate). Genişlik `.popup-dialog` üzerindeki `max-w-[...]`
ile belirlenir.

```blade
<div class="add-new-popup z-[999] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]" id="ajax-modal">
    <div class="popup-dialog flex transition-all max-w-[550px] min-h-full items-center mx-auto">
        <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[20px] md:mb-[25px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                <div class="trezo-card-title">
                    <h5 class="!mb-0" id="ajax-modal-title">Başlık</h5>
                </div>
                <div class="trezo-card-subtitle">
                    <button type="button" class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500" data-modal-close>
                        <i class="ri-close-fill"></i>
                    </button>
                </div>
            </div>
            <div class="trezo-card-content pb-[20px] md:pb-[25px]" id="ajax-modal-body"></div>
        </div>
    </div>
</div>
```

`ajax-modal.blade.php` iskeleti proje sahibi tarafından kurulmaktadır.
Dosya doluysa **onun** yapısına uy; `core/modal.js`'i ona göre yaz.
Modal gövdesi (`modals/form.blade.php`) sadece `<form>` ve alanlarını içerir,
kart kabuğunu tekrar etmez.

## Medya tarayıcısı — dosya yöneticisi

`/admin/media` sayfası ve form içinden açılan seçici modal aynı markup'ı
paylaşır: `resources/views/admin/pages/media/partials/browser.blade.php`,
davranışı `core/media-browser.js` verir. Yeni bir yerde medya listesi
gerekiyorsa bu partial'ı include et, ayrı bir ızgara yazma:

```blade
@include('admin.pages.media.partials.browser', [
    'selectable' => true, 'manageable' => true,
])
```

Klasör ağacı sidebar'ı değil — Google Drive tarzı: klasörler dosyalarla aynı
ızgarada kart olarak durur, üstte breadcrumb (`Medya > Blog > Kapak`) gezinme
sağlar, çift tıklama klasöre girer/dosyanın popup önizlemesini açar. Sağ tık
context menu açar (klasör/dosyaya göre farklı seçenekler: Aç, Yeniden
Adlandır, Taşı, Yeniden Kırp, İndir, Sil). Kart sürükleyip bir klasör
kartının veya breadcrumb kırıntısının üstüne bırakmak taşır; işletim
sisteminden dosya sürüklemek (üstüne bırakılan yer bir klasör kartıysa
doğrudan o klasöre) yükler. Ctrl/Cmd+tık ve Shift+tık ile çoklu seçim
yapılır, seçim varken araç çubuğunun altında bir toplu işlem çubuğu
(Taşı/Sil/Temizle) belirir — `selectable` açıksa ve seçim tek bir dosyaysa
buraya "Bu Dosyayı Seç" butonu da eklenir. Araç çubuğunda ayrıca ızgara/liste
görünüm anahtarı var (`this.view`, `localStorage`'da kalıcı) — ikisi de her
`load()`'da birlikte doldurulur, sadece hangisinin görünür olduğu değişir.

`selectable` ve `manageable` artık birbirini dışlamıyor — picker modalı da
tam yönetime sahip (context menu, sürükle-taşı, klasör oluşturma), fark
sadece `selectable`'ın seçim sonucu döndürüp döndürmediği.

**Sidebar** (`partials/sidebar.blade.php`, sadece `/admin/media` sayfasında —
picker modalında yok) kozmetik bir klasör ağacı DEĞİL, `MediaBrowser`'ın
gezinme API'sini (`goToRoot()`, `goToFolder(id, name)`, `showRecent()`,
`showUnattached()`) çağıran gerçek kısayollar: kök klasörler, "Son
Eklenenler" (tüm klasörlerde `created_at desc`), "Bağlantısız" (hiçbir kayda
bağlı olmayan dosyalar) ve gerçek bir depolama özeti (`GET
/admin/media/stats`). Bağlama `pages/media/index.js` yapar, `MediaBrowser`
sınıfının kendisi sidebar'ın var olup olmadığını bilmez.

Kartların markup'ı `media-browser.js` içinde üretilir; oradaki class'lar da
Tailwind build'i tarafından taranır (`@source` JS'i kapsar).

**Yeni yardımcı modaller** (hepsi `confirm.js` ile aynı promise dönen kalıp,
`core/` altında): `prompt.js` (`promptText()` — tek satır metin, klasör
oluşturma/yeniden adlandırma), `folder-picker.js` (`folderPicker.open()` —
"Taşı" diyaloğundaki iç içe klasör ağacı, `/admin/media/folders/tree`'yi
render eder), `media-preview.js` (`mediaPreview.open()` — çift tıkla açılan
popup önizleme, aksiyon adını döndürür, gerçek işlemi çağıran taraf yapar).

**Popup z-index katmanı** — biri diğerinin içinden açılabildiği için sıra
önemli: `ajax-modal` 999 < `media-picker-modal` 1003 < `confirm`/`prompt`/
`folder-picker`/`media-preview` 1004 < `crop-modal`/`ai-generator` 1005. Yeni
bir promise-döndüren popup eklerken bu sıraya uy; en azından üstünde
açılabileceği her şeyden yüksek bir z-index seç.

## İkonlar

İki set birlikte kullanılır:

- **Material Symbols** — `<i class="material-symbols-outlined">edit</i>` (menü, aksiyonlar)
- **Remixicon** — `<i class="ri-close-fill"></i>` (kapatma, sıralama okları)

Template'te bir bileşen hangi seti kullanıyorsa aynısını kullan.
Remixicon isimleri için `remixicon.html`.

## Dark mode

Her renk verilen elemana dark karşılığı yazılır. Yerleşik eşlemeler:

| Açık | Koyu |
|---|---|
| `bg-white` | `dark:bg-[#0c1427]` |
| `bg-gray-50` | `dark:bg-[#15203c]` |
| `border-gray-100` / `border-gray-200` | `dark:border-[#172036]` |
| `text-black` | `dark:text-white` |
| `hover:bg-gray-50` | `dark:hover:bg-[#15203c]` |

Dark varyantı olmayan bir renkli eleman bırakma.

## Sidebar

Sidebar `config/admin-menu.php`'den üretilir; `sidebar.blade.php` elle
düzenlenmez. Yeni modül eklerken config'e giriş yaz:

```php
[
    'title' => 'Blog',
    'icon'  => 'article',              // material symbols adı
    'permission' => 'blog.view',
    'children' => [
        ['title' => 'Yazılar',     'route' => 'admin.blog.index',          'permission' => 'blog.view'],
        ['title' => 'Kategoriler', 'route' => 'admin.blog-category.index', 'permission' => 'blog-category.view'],
    ],
],
```

Tek sayfalık modülde `children` yerine doğrudan `route` verilir.
Aktiflik `request()->routeIs()` ile hesaplanır.

## RTL

Template baştan sona `ltr:` / `rtl:` çiftleriyle yazılmıştır. Proje tek yönlü
(LTR) çalışsa da kopyalanan markup'taki `rtl:` class'larını **silme** —
template ile tutarlılık bozulur ve ileride yön desteği gerekirse iş çıkar.

## Kontrol listesi

- [ ] Markup template'ten alındı, class uydurulmadı
- [ ] Renkli her elemanın `dark:` karşılığı var
- [ ] Kart yapısı `trezo-card` > `header`/`content`/`footer` hiyerarşisine uyuyor
- [ ] Form alanları component ile yazıldı, ham class dizisi kopyalanmadı
- [ ] Her input'un altında `data-error="<alan>"` span'ı var
- [ ] Sayfa JS'i `@push('admin.scripts')` ile `type="module"` olarak eklendi
- [ ] Sidebar girişi `config/admin-menu.php`'ye eklendi
- [ ] Arayüz metinleri Türkçe
