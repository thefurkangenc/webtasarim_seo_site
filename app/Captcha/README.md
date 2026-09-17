# Captcha

Formlara gönderim öncesi robot kontrolü ekler. Tamamen kendi sunucunda çalışır:
Google/Cloudflare gibi bir servise hesap açmak, anahtar girmek ya da composer
paketi kurmak gerekmez.

Görünüm Google'ın "Ben robot değilim" kutusuyla aynı kalıp: küçük bir onay
kutusu formun akışında durur, tıklanınca bulmaca ayrı bir popup'ta açılır.
Sayfa yüklenirken hiçbir şey üretilmez — bulmaca yalnızca kutuya tıklanınca
istenir, popup çözülünce kısa bir "Doğrulandı" gösterip kendiliğinden kapanır.

Varsayılan doğrulama **kaydırmalı yapboz**: ziyaretçi eksik parçayı boşluğa
oturtur. Görsel her seferinde koddan çizilir, hazır görsel klasörü yoktur.

Popup paneli JS tarafından `<body>`'ye taşınır (onay kutusu ve gizli alan
formda kalır) — form bir açılır pencerenin (bülten popup'ı gibi) içinde olsa
bile üst katman o kapsayıcı tarafından kırpılmaz.

## Kullanım

```blade
<x-captcha form="contact" />
```

Bileşen kendi css/js'ini de getirir; layout'a hiçbir şey eklenmez. Doğrulama
kapalıysa hiçbir şey basmaz.

FormRequest tarafı:

```php
use App\Captcha\CaptchaManager;
use App\Captcha\Concerns\VerifiesCaptcha;

class ContactSubmitRequest extends FormRequest
{
    use VerifiesCaptcha;

    protected function captchaForm(): ?string
    {
        return 'contact';
    }

    public function rules(): array
    {
        return [
            // ...
            CaptchaManager::FIELD => $this->captchaRules(),
        ];
    }
}
```

Hepsi bu. Formun JS'inde değişiklik gerekmez: bilet gizli bir input'ta durur,
`FormData` ile birlikte gider, `form.reset()` bileşeni de sıfırlar.

## Başka bir projeye taşıma

`app/Captcha` klasörünü kopyala, sonra iki satır ekle:

```php
// bootstrap/providers.php
App\Captcha\CaptchaServiceProvider::class,
```

```php
// bootstrap/app.php > withRouting(then:) — catch-all route'lardan ÖNCE
Route::middleware('web')->group(base_path('app/Captcha/routes.php'));
```

Ayarlar `app/Captcha/config.php` içindedir, `config/` altına dosya eklemek
gerekmez. Panel entegrasyonu isteğe bağlıdır (aşağıya bak).

## Akış

```
<x-captcha />        ziyaretçi forma dokununca bulmacayı ister
GET  /captcha/challenge   -> görseller + imzalı jeton   (doğru konum SUNUCUDA kalır)
POST /captcha/verify      -> cevabı dener, doğruysa "bilet" verir
form gönderimi            -> gizli "captcha" alanındaki bilet doğrulanır
```

Güvenlik kararları:

| Ne | Neden |
|---|---|
| Doğru konum cache'te, jetonda değil | İmzalı jeton okunabilir base64'tür; sır oraya konsaydı tarayıcı çözüp okurdu |
| Bulmaca tek denemede tükenir (`Cache::pull`) | Aynı görsel üzerinde deneme yanılma yapılamaz |
| Süre ve hareket sayısı eşiği | Formu anında dolduran, tek sıçramada çözen otomasyon elenir |
| Bilet tek kullanımlık | Bir kez çözüp aynı bileti bin kez göndermek engellenir |
| Bilet **passedValidation**'da yakılır | Başka bir alan hata verdiğinde kullanıcı bulmacayı baştan çözmez |
| Kural `public bool $implicit = true` | Laravel özel kuralları, alan istekte hiç yoksa atlar — bayrak olmadan alanı göndermeyen bot doğrulamayı tamamen es geçerdi |

## Panel entegrasyonu (bu projede kurulu)

`App\Support\Settings` varsa `App\Captcha\Support\PanelSettings` köprüsü
`captcha` ayar grubunu okur ve config'i ezer. Sınıf yoksa köprü sessizce
devre dışı kalır — klasör Settings'i olmayan bir projede de çalışır.

Panel tarafındaki dosyalar (taşınırken bunlar da isteniyorsa kopyalanır):

```
config/settings.php                                   > groups.captcha + defaults.captcha
config/permissions.php                                > setting.captcha.update
config/form-help.php                                  > captcha.*
routes/admin.php                                      > setting/captcha PUT
app/Http/Requests/Admin/Setting/SettingCaptchaRequest.php
app/Http/Controllers/Admin/Setting/SettingController.php  > updateCaptcha()
resources/views/admin/pages/setting/tabs/captcha.blade.php
```

## Yeni sürücü eklemek

`Contracts\Driver`'ı uygulayan bir sınıf yaz, `config.php`'deki `drivers` ve
`labels` dizilerine bir satır ekle, `resources/views/drivers/` altına blade'ini
koy. Manager ve kural değişmez; panelden seçilebilir hale gelir.

`Driver::challenge()` bir `Challenge` döner: `payload` tarayıcıya gider,
`secret` sunucuda kalır ve `solved()`'a geri verilir.

## Ayarlar

`app/Captcha/config.php` — sürücü, hangi formlarda çalışacağı, jeton/bilet
ömrü, istek sınırı, yapboz ölçüleri ve davranış eşikleri.

Paneldeki **Ayarlar › Ziyaretçi Deneyimi › Güvenlik Doğrulaması** sekmesi
şunları ezer: açık/kapalı, sürücü, dört form anahtarı, kabul edilen sapma.
