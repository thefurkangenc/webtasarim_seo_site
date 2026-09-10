---
name: admin-js
description: Use when writing or editing JavaScript under public/admin/assets/js/ - AJAX tables, ajax modals, form submission, toasts, delete confirmation, page scripts. Defines the native ES module architecture, the core/ helper APIs and the no-jQuery rule for the admin panel.
---

# Admin JavaScript

Native ES modules. **Build adımı yok** — tarayıcı `import`'u doğrudan çözer.
Bu yüzden her `<script>` etiketi `type="module"` olmalıdır.

**jQuery kullanılmaz.** Template'in `custom.js` dosyası da salt JS'tir; onu
takip et.

## Dosya düzeni

```
public/admin/assets/js/
    custom.js                 <- template'in kendi dosyası, DOKUNMA
    charts-custom.js          <- template'in kendi dosyası, DOKUNMA
    vendor/cropper/           <- Cropper.js v1 yerel kopyası, DOKUNMA
    core/
        http.js               fetch sarmalayıcı
        form.js               form serileştirme + hata boyama
        modal.js              AjaxModal
        table.js              DataTable
        toast.js              bildirim
        confirm.js            silme onayı
        prompt.js             tek satır metin girişi (confirm.js kalıbı)
        folder-picker.js      "Taşı" diyaloğundaki klasör ağacı seçici
        media-preview.js      dosyaya çift tıklayınca açılan popup önizleme
        cropper.js            kırpma modalı
        media-browser.js      dosya yöneticisi (breadcrumb, context menu, sürükle-taşı, çoklu seçim)
        media-picker.js       kütüphaneden seçme modalı (media-browser.js'i modalda sürer)
        media-field.js        <x-admin::form.image> davranışı (layout'ta yüklü)
    pages/
        blog/
            index.js          liste sayfası
            show.js           detay sayfası (gerekirse)
```

Modül klasörü kebab-case: `blog-category/index.js`.

## Yükleme

```blade
@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/blog/index.js') }}"></script>
@endpush
```

Sayfa JS'i içinden:

```js
import { DataTable } from '../../core/table.js';
import { AjaxModal } from '../../core/modal.js';
```

## core/ API'leri

Bu imzalar sözleşmedir. Değiştirmen gerekirse tüm çağıranları da güncelle.

### http.js

```js
http.get(url, params = {})       // params query string'e çevrilir, boş değerler atılır
http.post(url, body)             // FormData veya düz nesne
http.put(url, body)              // FormData ise _method=PUT enjekte edip POST atar
http.patch(url, body)
http.delete(url)
http.html(url)                   // -> Promise<string>, Blade parçası (modal gövdesi)

escapeHtml(value)                // innerHTML'e basılan her kullanıcı verisi için
```

**Dönen değer sunucunun JSON gövdesinin tamamıdır**, `data` alanı tek başına değil:

```js
const { message } = await http.delete(url);              // mesaj
const { data, meta } = await http.get(endpoint, params); // liste + sayfalama
```

- `X-CSRF-TOKEN` header'ını `<meta name="csrf-token">`'dan otomatik ekler.
- `X-Requested-With: XMLHttpRequest` gönderir, çerezleri taşır.
- 422 + `errors` → `ValidationError` (`.errors` taşır).
- 419 → "Oturumunuz sona erdi", 401 → "Oturumunuz kapandı".
- Diğer hatalarda `HttpError` (`.message`, `.status`, `.payload`).

`meta` etiketi `admin/layout/partials/meta.blade.php` içinde tanımlıdır.

### form.js

```js
serialize(formEl)                     // -> FormData (dosyalar dahil)
clearErrors(formEl)                   // data-error span'larını ve kırmızı kenarları temizler
showErrors(formEl, errors)            // { field: ["mesaj"] } -> ilgili span'lara basar
setLoading(buttonEl, isLoading)       // butonu disable eder / metnini değiştirir
```

`showErrors` her alan için `[data-error="<isim>"]` span'ını doldurur ve
input'a `!border-danger-500` ekler.

> **Dikkat:** `!border-danger-500` derlenmiş `style.css` içinde **yoktur** ve
> template'te de geçmez — Tailwind build kurulmadan bu class hiçbir şey yapmaz.
> Yeni bir utility class yazdığında bu ihtimali her zaman düşün:
> `grep -c 'class-adi' public/admin/assets/css/style.css` ile doğrula,
> yoksa `npm run admin:css` çalıştır.

### modal.js

```js
const modal = new AjaxModal();

await modal.open(url, { title: 'Yeni Blog Yazısı' });   // içeriği #ajax-modal-body'ye basar
modal.close();
modal.onSubmit(async (formEl) => { ... });               // form submit'ini yakalar
```

- Modal kökü `#ajax-modal`; açılış `.active` class'ı eklenerek yapılır (template mekanizması).
  İskelet `resources/views/admin/layout/modals/ajax-modal.blade.php` içinde ve
  admin layout'unda include edilir. Sayfada yoksa `modal.js` aynı markup'ı
  çalışma anında oluşturur.
- `[data-modal-close]` elemanları ve backdrop tıklaması kapatır; `Escape` de kapatır.
- Modal gövdesi her açılışta baştan yazılır — eski event listener kalmaz.
- `onSubmit`'e verdiğin fonksiyon **sadece isteği atar**. Yükleniyor durumu,
  `clearErrors`, `ValidationError` → `showErrors` ve diğer hatalar → `toast.error`
  akışı `modal.js` içinde halledilir; sayfa JS'inde `try/catch` yazma.

### table.js

```js
const table = new DataTable({
    endpoint: '/admin/blog/datatable',
    body:     document.querySelector('#blog-table-body'),
    search:   document.querySelector('#blog-search'),
    filters:  { category_id: document.querySelector('#filter-category') },
    row:      (item) => `<tr>...</tr>`,
    empty:    'Kayıt bulunamadı.',
});

table.load();      // ilk yükleme
table.reload();    // mevcut görünümü tazeler (kaydetme sonrası)
```

`table.js` ayrıca `cell(content, extra)` yardımcısını dışa verir; satır
render'ında hücre class dizisini tekrar yazmamak için kullan:

```js
import { DataTable, cell } from '../../core/table.js';

row: (blog) => `<tr>${cell(escapeHtml(blog.title))}${cell(actions(blog), '!text-right')}</tr>`
```

- Arama 300 ms debounce'lu.
- `data-column` taşıyan `<th>`'lere tıklama sıralamayı çevirir.
- Sayfalama `pagination.html` kalıbıyla render edilir.
- İstek parametreleri: `search`, `sort`, `direction`, `page`, `per_page` + filtreler.
  Bunlar `<Modul>FilterRequest` ile birebir eşleşmelidir.

### cropper.js

```js
import { cropModal } from '../../core/cropper.js';

const crop = await cropModal.open(file, { preset: 'blog.cover', width: 1200, height: 630, label: 'Blog Kapak' });
// crop === null  -> vazgeçildi
// crop === { x, y, width, height, rotate, scaleX, scaleY }
```

Cropper.js ilk kullanımda tembel yüklenir. Sunucu bu koordinatları
`rotate → flip → crop` sırasıyla uygular; sıra değiştirilirse çıktı bozulur.

Modal araç çubuğu: zoom-in/zoom-out butonları + bir zoom slider'ı
(`data-crop-zoom-range`, fare tekerleği/pinch zoom ile de senkron kalır),
90° sağ/sol döndürme, yatay/dikey çevirme, seçim kutusunu zoom'a dokunmadan
ortalayan `center`, ve tam sıfırlama (`reset`). `open(file, options)`'a
verilen `file` her zaman kırpımın **kaynağı** olmalı — kırpılmış bir sonucu
tekrar bu fonksiyona verirsen (bkz. `media-field.js`'teki `onRecrop` notu)
her seferinde biraz daha fazla kırpar.

### media-picker.js / media-browser.js

```js
const media = await mediaPicker.open();      // seçilen medya payload'ı ya da null

new MediaBrowser(root, { onSelect, onOpen }); // browser.blade.php markup'ını sürer
```

`MediaBrowser` bir dosya yöneticisidir — sidebar klasör ağacı yok, klasörler
dosyalarla aynı ızgarada kart, gezinme `this.path` (breadcrumb) state'i ile
istemci tarafında tutulur. Klasör listesi `GET /admin/media/folders?parent_id=`
(tek seviye, `MediaFolderService::children()`), "Taşı" diyaloğu tam ağacı
`GET /admin/media/folders/tree`'den çeker. Çoklu seçim `this.selection`
(`Set<'folder:5'|'media:12'>`), toplu taşıma/silme sunucuda
`POST /admin/media/bulk-move` / `bulk-delete` — ikisi de `{media: [], folders:
[]}` alır, dolu bir klasör silinmeye çalışılırsa güvenlik kuralı gereği
atlanır ve `skipped` dizisinde sebebiyle raporlanır (bkz.
`MediaFolderService::delete()` — istemci bunu önceden tahmin etmeye
çalışmaz, sunucu cevabına güvenir).

Sağ tık `contextmenu` olayını dinler, `openMenu()` hedefe göre (boş alan /
tek klasör / tek dosya / çoklu seçim) farklı bir aksiyon listesi kurar.
Sürükle-taşı `dragstart`'ta seçili öğeleri `application/x-media-items`
mime type'ıyla `dataTransfer`'a yazar; bir klasör kartı veya breadcrumb
kırıntısı bunu `drop`'ta yakalayıp `bulk-move` çağırır — işletim sisteminden
gelen gerçek dosya sürüklemesi (`Files` type) ayrı bir dropzone handler'ında
kalır, ikisi karışmaz.

### media-field.js

`<x-admin::form.image>` alanının davranışı. **Sayfa JS'inden çağrılmaz** —
layout'ta yüklüdür ve `document` seviyesinde olay delegasyonu ile çalışır,
ajax modal içinde açılan formlarda da kendiliğinden devreye girer.

Alan seçilen görseli hemen yükler ve gizli input'a `media_id` yazar; form
gönderildiğinde sunucuya sadece bu id gider. Sürükle-bırak, tıklamayla dosya
seçme ve kütüphaneden seçme aynı `render()` akışından geçer.

Her değişimde (yükleme/seçim/yeniden kırpma) alanın kökünde `media:change`
custom event'i fırlatılır (`bubbles: true`, `detail` = medya payload'ı ya da
`null` kaldırıldıysa). Başka bir core modülü bir görsel alanını programatik
doldurmak isterse `setFieldMedia(root, media)` export'unu kullanır —
`core/seo-field.js`'in kapak görselinden paylaşım görselini eşzamanlı
doldurması bunun üzerine kurulu.

`render()` her çağrıda `[data-media-actions]`'ın `grid-cols-2`/`grid-cols-3`
sınıfını ve `[data-media-image]`'in `data-original` attribute'unu yeniden
hesaplar — ikisi de blade'in ilk render'ıyla aynı mantığı takip eder (bkz.
`trezo-ui` skill'inin "Görsel alanı önizlemesi" bölümü). Yeni bir `media`
payload alanı eklersen (ör. sunucudan) `Media::toPayload()` üzerinden geçtiği
için ekstra bir kablolamaya gerek kalmaz.

### toast.js / confirm.js

```js
toast.success('Kayıt oluşturuldu.');
toast.error('Bir hata oluştu.');

if (await confirm('Bu yazı silinecek. Emin misiniz?')) { ... }
```

## Sayfa JS şablonu

```js
import { DataTable } from '../../core/table.js';
import { AjaxModal } from '../../core/modal.js';
import { http } from '../../core/http.js';
import { toast } from '../../core/toast.js';
import { confirm } from '../../core/confirm.js';

const modal = new AjaxModal();

const table = new DataTable({
    endpoint: '/admin/blog/datatable',
    body: document.querySelector('#blog-table-body'),
    search: document.querySelector('#blog-search'),
    row: (blog) => `
        <tr>
            <td class="ltr:text-left rtl:text-right px-[20px] py-[13px] border-b border-gray-100 dark:border-[#172036]">${blog.title}</td>
            <td class="ltr:text-left rtl:text-right px-[20px] py-[13px] border-b border-gray-100 dark:border-[#172036]">
                <button type="button" data-action="edit" data-id="${blog.id}">Düzenle</button>
                <button type="button" data-action="delete" data-id="${blog.id}">Sil</button>
            </td>
        </tr>`,
});

table.load();

// Tek delegasyonlu listener — satır başına listener bağlama
document.querySelector('#blog-table-body').addEventListener('click', async (event) => {
    const button = event.target.closest('[data-action]');
    if (!button) return;

    const { action, id } = button.dataset;

    if (action === 'edit') {
        await modal.open(`/admin/blog/form/${id}`, { title: 'Blog Yazısını Düzenle' });
    }

    if (action === 'delete' && await confirm('Bu yazı silinecek. Emin misiniz?')) {
        const { message } = await http.delete(`/admin/blog/${id}`);
        toast.success(message);
        table.reload();
    }
});

document.querySelector('#blog-create').addEventListener('click', () => {
    modal.open('/admin/blog/form', { title: 'Yeni Blog Yazısı' });
});

modal.onSubmit(async (form) => {
    const { message } = await http.post(form.action, new FormData(form));
    toast.success(message);
    modal.close();
    table.reload();
});
```

Bu dosya 40-60 satırda bitmelidir. Uzuyorsa ağır iş `core/`'a taşınmalıdır.

## Paylaşılan core modülleri

| Dosya | Ne verir |
|---|---|
| `core/editor.js` | `initEditors(root)` — `[data-editor]` alanlarını TinyMCE'ye çevirir. Sayfa JS'i gerektirmez; modal içinde açılan bir editör için `initEditors(modal.body)` çağır. |
| `core/seo-field.js` | `initSeoFields(root)` — sayaçlar ve Google önizlemesi. Modal gövdesinde SEO varsa modal açıldıktan sonra çağrılmalı. |
| `core/tag-input.js` | `initTagInputs(root)` — etiket alanı. Gizli `name="tags[]"` input'ları üretir, ayrı serileştirme gerekmez. |
| `core/ai-generator.js` | `aiGenerator.open(key, { defaults })` → `Promise<object\|null>`. Üretimi kuyruğa atar, durumu sorar, JSON çıktıyı döndürür. |
| `core/select.js` | `[data-choices]` select'lerini Choices.js'e çevirir — form component'i bunu otomatik ekler. |
| `core/datepicker.js` | `[data-datepicker]` alanlarını Flatpickr'a çevirir (Türkçe, `altInput`). |
| `core/table.js` `reorder` seçeneği + `reorderHandle()` | `DataTable`'a sürükle-bırak sıralama ekler — bkz. trezo-ui skill'i "Sürükle-bırak sıralama". |
| `core/activity-log.js` | `historyButton(subjectType, id)` — satır aksiyonlarına "Geçmiş" ikonu ekler; `<x-admin::activity-log-button>` bileşeni bu dosyayı kendiliğinden yükler, sayfa JS'i yalnızca `historyButton` importunu satır şablonuna ekler. |

```js
const output = await aiGenerator.open('blog.content', { defaults: { title } });

if (output) {
    form.querySelector('[name="title"]').value = output.title;
    window.tinymce.get('content')?.setContent(output.content);
}
```

Üretilen değerleri alanlara yazdıktan sonra SEO sayacı ve önizlemesinin
güncellenmesi için ilgili input'lara `input` olayı gönder:

```js
form.querySelectorAll('[data-seo-input]')
    .forEach((input) => input.dispatchEvent(new Event('input', { bubbles: true })));
```

## Tailwind class'ını şablon değişkeniyle kurma

Derleyici kaynak dosyaları metin olarak tarar; `bg-${variant}-100` gibi
parçalanmış bir ad bulunamaz ve o class hiç derlenmez. Tam metin yaz:

```js
const BADGES = {
    success: 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500',
    danger: 'bg-danger-100 dark:bg-[#15203c] text-danger-600 dark:text-danger-500',
};
```

## Kurallar

1. **jQuery yok.** `$`, `$.ajax`, `.on()` görürsen kaldır.
2. **Event delegation kullan.** Tablo satırları JS ile yeniden basıldığı için
   satır başına listener bağlamak bozulur; kapsayıcıya tek listener bağla.
3. **`innerHTML`'e ham kullanıcı verisi basma.** Metin alanlarını
   `core/http.js`'in sağladığı `escapeHtml()` ile geçir; HTML gerektiren
   alanlarda (TinyMCE içeriği) sunucuda temizlenmiş olduğundan emin ol.
4. **Hataları yutma.** `http.*` çağrılarında `ValidationError` modal formunda
   `showErrors` ile gösterilir; diğerleri `toast.error` ile.
5. **`custom.js` ve `charts-custom.js`'i düzenleme.** Bunlar template'e aittir;
   yeni davranış `core/` veya `pages/` altına yazılır.
6. **Sabit URL yazma zorunluluğu var** — JS'te `route()` yoktur. Endpoint'leri
   Blade'den `data-*` ile geçir ya da sayfa JS'inin en üstünde tek yerde tanımla.

## Kontrol listesi

- [ ] Script `type="module"` ile yüklendi
- [ ] jQuery kullanılmadı
- [ ] Tablo/satır aksiyonları event delegation ile bağlandı
- [ ] Form hataları `data-error` span'larına basılıyor
- [ ] Kaydetme sonrası `table.reload()` çağrılıyor
- [ ] Sayfa JS'i sadece modüle özel mantığı içeriyor
- [ ] DataTable parametreleri ilgili FilterRequest ile eşleşiyor
