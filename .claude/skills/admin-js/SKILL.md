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
    core/
        http.js               fetch sarmalayıcı
        form.js               form serileştirme + hata boyama
        modal.js              AjaxModal
        table.js              DataTable
        toast.js              bildirim
        confirm.js            silme onayı
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
http.get(url, params = {})       // -> Promise<data>
http.post(url, body)             // FormData veya düz nesne
http.put(url, body)              // FormData ise _method=PUT enjekte eder
http.delete(url)                 // -> Promise<data>
```

- `X-CSRF-TOKEN` header'ını `<meta name="csrf-token">`'dan otomatik ekler.
- `X-Requested-With: XMLHttpRequest` gönderir.
- 2xx'te `data` alanını (yoksa gövdenin tamamını) çözümler.
- 422'de `ValidationError` fırlatır (`.errors` taşır).
- 403/500'de `HttpError` fırlatır (`.message`, `.status` taşır).

`meta` etiketi `admin/layout/partials/meta.blade.php` içinde bulunmalıdır.

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
- `[data-modal-close]` elemanları ve backdrop tıklaması kapatır; `Escape` de kapatır.
- Modal gövdesi her açılışta baştan yazılır — eski event listener kalmaz.

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
table.reload();    // mevcut sayfayı tazeler (kaydetme sonrası)
```

- Arama 300 ms debounce'lu.
- `data-column` taşıyan `<th>`'lere tıklama sıralamayı çevirir.
- Sayfalama `pagination.html` kalıbıyla render edilir.
- İstek parametreleri: `search`, `sort`, `direction`, `page`, `per_page` + filtreler.
  Bunlar `<Modul>FilterRequest` ile birebir eşleşmelidir.

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

## Kurallar

1. **jQuery yok.** `$`, `$.ajax`, `.on()` görürsen kaldır.
2. **Event delegation kullan.** Tablo satırları JS ile yeniden basıldığı için
   satır başına listener bağlamak bozulur; kapsayıcıya tek listener bağla.
3. **`innerHTML`'e ham kullanıcı verisi basma.** Metin alanlarını
   `core/http.js`'in sağladığı `escapeHtml()` ile geçir; HTML gerektiren
   alanlarda (Quill içeriği) sunucuda temizlenmiş olduğundan emin ol.
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
