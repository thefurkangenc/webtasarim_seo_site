# Video Oynatıcı Tasarımı

## Amaç

Kütüphanedeki `mp4`/`webm` için YouTube benzeri özel oynatıcı; YouTube/Vimeo
adresleri için sağlayıcının kendi iframe'i. Oynatıcı hem ön yüzde hem
adminde kullanılır. Kalite menüsü ve süre-çubuğu kare önizlemesi yalnızca
dosyada vardır: yükleme (ve mevcut dosyalar) kuyrukta `ffmpeg` ile 1080 /
720 / 480 + poster + sprite üretir. Orijinal dosya hemen oynar, kopyalar
bitince menü dolar.

Paket yok (Plyr, Video.js, php-ffmpeg yok). `ffmpeg`/`ffprobe` sistem
ikiliğidir; PHP `Illuminate\Support\Facades\Process` ile çağırır.

## Kararlar

| Konu | Karar | Gerekçe |
|---|---|---|
| Dünyalar | Ortak JS çekirdeği, ayrı Blade + CSS | Ön yüz Bootstrap, admin Tailwind; stil karışmaz, davranış tek yerde |
| Kaynak | Adres **veya** dosya, ikisi birden değil | Mevcut `<x-admin::form.video>` sözleşmesi |
| Gömme arayüzü | YouTube/Vimeo native iframe | Özel krom ToS/API kırılgan; dosyada tam kontrol |
| Kalite | `ffmpeg` 1080/720/480, yukarı ölçekleme yok | Tek dosyadan menü uydurulmaz; gömmede menü yok |
| Hover önizleme | Sprite + WebVTT; bitmeden yalnız zaman | YouTube karesi; kuyruk bitene kadar yalan kare yok |
| İşleme anı | Orijinal hemen oynar, JSON tek seferde `ready` | Ziyaretçi beklemez; ara kayıt yok |
| Eski dosyalar | `video:process` kuyruğa alır | Kütüphane boş kalmasın |
| Admin kapsamı | Form önizlemesi, medya popup, her video kutusu | Kullanıcı tam oynatıcı istedi; form kutusu 16:9 yükseltilir |
| Otomatik test | Yok | Proje kuralı; doğrulama manuel |

## Dışında kalanlar

Altyazı, tiyatro modu, kaydırınca mini oynatıcı, bölümler, HLS, YouTube
IFrame API ile özel krom, kalite menüsünün gömmede görünmesi, PHP ffmpeg
paketi, iki dünya arasında CSS/markup paylaşımı.

## Mimari

İki katman yalnızca `media.video` JSON'u ve public dosya URL'leriyle konuşur.

```
Yükleme ─► MediaService::store (storeRaw)
              │
              ├─ media.path = orijinal (hemen oynar)
              └─ video.status = processing ─► ProcessVideoJob
                                                │
                                                ▼
                                         VideoProcessor (ffprobe, ffmpeg)
                                                │
                                                ▼
                                         video.status = ready | failed
                                                │
Ön yüz / admin Blade ─► [data-player] ─► VideoPlayer (poll GET /media/{id}/player)
```

Görsel `conversions` (thumb/medium) karışmaz. Gömme `VideoPlayer` sınıfına
girmez: bileşen iframe basar.

## `media.video` JSON

`media` tablosuna nullable JSON kolon `video`. Cast: `array`.

```php
[
    'status'   => 'processing' | 'ready' | 'failed',
    'duration' => 123.45,          // saniye, ffprobe
    'width'    => 1920,
    'height'   => 1080,
    'poster'   => ['path' => 'uploads/…/uuid-poster.jpg'],
    'sprite'   => [                // yoksa veya üretim düştüyse null
        'path'      => 'uploads/…/uuid-sprite.jpg',
        'vtt'       => 'uploads/…/uuid-sprite.vtt',
        'interval'  => 2,
        'columns'   => 5,
        'width'     => 160,        // kare genişliği px
        'height'    => 90,         // en-boydan hesaplanır (16:9 ise 90)
    ],
    'renditions' => [
        '1080' => ['path' => 'uploads/…/uuid-1080.mp4', 'size' => 1234567],
        '720'  => ['path' => 'uploads/…/uuid-720.mp4', 'size' => 890123],
        // 480 yoksa anahtar yok — kaynak o boydan küçükse üretilmez
    ],
    'error' => null | 'Sunucuda ffmpeg yok.',
]
```

`Media::allPaths()` poster, sprite, VTT ve rendition `path` değerlerini de
döner; silme diskte türetilenleri temizler. `Media::toPayload()` içine
oynatıcının ihtiyaç duyduğu `video` özeti girer (URL'ler çözülmüş):

```php
'video' => $this->playerPayload(), // video değilse null
```

`playerPayload()` gövdesi (public JSON ve Blade config aynı şekil):

```php
[
    'src'        => $this->url(),          // orijinal
    'poster'     => string|null,           // url
    'duration'   => float|null,
    'status'     => 'processing'|'ready'|'failed'|null,
    'status_url' => string|null,           // processing ise route('media.player', $this)
    'qualities'  => [
        ['id' => '1080', 'label' => '1080p', 'src' => '…'],
        // id "source" yalnızca aynı boyda rendition YOKSA orijinal için
        ['id' => 'source', 'label' => '900p', 'src' => $this->url()],
    ],
    'sprite'     => [
        'url' => '…', 'vtt' => '…', 'interval' => 2,
        'columns' => 5, 'width' => 160, 'height' => 90,
    ] | null,
]
```

Kalite etiketinde "Orijinal" yazılmaz; etiket kaynağın veya rendition'ın
yüksekliğidir (`1080p`). Aynı yükseklikte ikinci kopya menüye girmez:
1080 kaynakta transcode `1080` üretildiyse menüde `source` satırı yoktur.

`processing` iken `qualities` boş dizi, `<video src>` orijinal, menü
"Hazırlanıyor". `failed` iken menü yok, orijinal oynar.

## İşleme

### Tetik

`MediaService::store()` video uzantısında (`mp4`, `webm`) kayıt oluşunca
`video = ['status' => 'processing']` yazar ve `ProcessVideoJob` basar.
Görsel akışına dokunulmaz. `MediaService` ffmpeg bilmez.

`ProcessVideoJob`: `ShouldBeUnique` (unique id = media id), `uniqueFor` =
900, `timeout` = 600, `tries` = 1. `failed()` yarım dosyaları siler,
kayıt duruyorsa `status = failed` + `error` yazar; `media.path` dokunulmaz.

### `VideoProcessor`

`App\Services\Media\VideoProcessor`. Tek giriş `process(Media $media): void`.

1. Kayıt yoksa veya video değilse return.
2. `ffprobe` süre / en / boy → `media.width` / `height` ve JSON.
3. Poster: ~1. saniye karesi, JPEG, `uuid-poster.jpg`.
4. Hedef yükseklikler `config('video.heights')` = `[1080, 720, 480]`.
   Kaynak yükseklik ≥ hedef ise H.264 `yuv420p` `+faststart` CRF 23, AAC
   128k, genişlik 2'ye bölünür, en-boy korunur. Çıktı her zaman `.mp4`
   (kaynak webm olsa da). Geçici dosyaya yaz, bitince asıl yola al.
5. Sprite: her 2 sn bir kare, 160px geniş, yükseklik kaynak en-boyundan
   (2'ye bölünür), 5 kolon, JPEG + WebVTT `xywh`. Sprite düşerse JSON
   `sprite = null`, status yine `ready` (rendition'lar olduysa).
6. Tek `saveQuietly`: `status = ready`, dolu JSON. Denetim kaydı kullanıcı
   düzenlemesi gibi görünmesin diye `LogsActivity` tetiklenmez; özet
   `Activity::record(logName: 'media', event: 'processed', …)`.

`ffmpeg`/`ffprobe` yok veya dosya okunamıyorsa `failed` + kısa Türkçe
`error` (`Sunucuda ffmpeg yok.` / `Video okunamadı.` / `Video işlenirken
zaman doldu.`). Yarım rendition/poster/sprite silinir.

### Config (`config/video.php`)

```php
return [
    'ffmpeg'  => env('FFMPEG_PATH', 'ffmpeg'),
    'ffprobe' => env('FFPROBE_PATH', 'ffprobe'),
    'heights' => [1080, 720, 480],
    'crf'     => 23,
    'audio_bitrate' => '128k',
    'sprite'  => [
        'interval' => 2,
        'width'    => 160,
        'columns'  => 5,
    ],
    'poster_at' => 1.0, // saniye
];
```

### Backfill

`php artisan video:process` — cron yok.

Kuyruğa alınanlar: `mp4`/`webm` ve (`video` boş veya `status` `failed`
veya `status` `processing` ve `updated_at` 15 dakikadan eski). `ready`
atlanır. Komut ffmpeg çalıştırmaz, yalnızca iş basar. Takılı `processing`
(işçi öldü, unique süresi geçti) böylece yeniden girer; iş hâlâ
koşuyorsa `ShouldBeUnique` ikinci kopyayı düşürür.

## Public JSON

```
GET /media/{media}/player    media.player    throttle:60,1
```

`routes/web.php`, catch-all `pages.php`'den önce (mevcut yükleme sırası
yeter). `ReservedPath` `media` segmentini rezerve eder.

`App\Http\Controllers\Media\PlayerController@show` — ince, Eloquent yok
serviste: model `isVideo()` değilse `abort(404)`, `success()` ile
`playerPayload()`. Yükleyen, klasör, orijinal ad yok. Auth yok: dosyalar
zaten public diskte. Video olmayan id 404.

Ayrı admin endpoint açılmaz; form poll ve ön yüz aynı URL.

## Oynatıcı bileşeni

### API

Ön yüz: `<x-player :media="$file" :embed="$embed" :title="$title" />`  
Admin: `<x-admin::player :media="$file" :embed="$embed" :title="$title" compact />`

`embed` (VideoEmbed dizisi) varsa 16:9 iframe (`youtube-nocookie` /
Vimeo, `allowfullscreen`, başlık `title`). `media` varsa `[data-player]`.
İkisi de yoksa hiçbir şey. Çağıran tek kaynak geçirir (form zaten tek
kaynak). `compact` tam çubuk, kutu 16:9 (formdaki 220px tavan kalkar).

Mevcut çağrı yerleri:

- `pages/projects/show.blade.php` native iframe/`<video controls>` → `<x-player>`
- `resources/views/admin/components/form/video.blade.php` önizleme
- `video-field.js` yükleme/seçim sonrası iskelet
- `media-preview.js` `is_video` (bugün ikon basıyor) → `[data-player]`

### Markup sözleşmesi

Kök `[data-player]`. Native `<video>` (`controls` yok, `playsinline`,
`preload="none"`). `src` ilk oynatmaya kadar yazılmaz. Poster ayrı katmanda.
Oynatılmamışken (`is-pristine`) alt çubuk gizlidir; yalnızca thumbnail +
ortadaki play. Tıklanınca kaynak yüklenir, çubuk gelir. Config:

```html
<script type="application/json" data-player-config>…playerPayload + title…</script>
```

Kullanıcı metni JSON'da; innerHTML'e basılırken kaçış `prefs`/`player`
tarafında, admin `escapeHtml` ile aynı kural.

### Davranış

Çubuk: play/pause, −10sn / +10sn, süre (oynanan + tampon), `şu an / toplam`,
ses ikon + slider, dişli (hız 0.5 / 0.75 / 1 / 1.25 / 1.5 / 2; kalite
yalnızca `qualities` doluysa), PiP (`pictureInPictureEnabled` yoksa gizli),
tam ekran. Durunca ve hover'da görünür; oynarken ~2.5 sn hareketsizlikte
gizlenir (`prefers-reduced-motion` gizlemez). iOS'ta ses slider'ı etkisiz,
mute çalışır.

Oynatılmamışken (`is-pristine`) alt çubuk yoktur; poster + ortadaki play.
İlk tık (veya Space/K) `src`'yi yazar ve çubuğu açar. Çift tık ±10sn/tam
ekran, ilk jestten sonraki ~400 ms içinde yok sayılır.

İşaret: tek tık oynat/duraklat. Çift tık sol üçte bir −10sn, sağ +10sn,
orta tam ekran. Süre çubuğu tık/sürükle. Hover: sprite yoksa zaman, varsa
VTT + kare.

Klavye (yalnızca odak): Space/K oynat-duraklat, J/← −10sn, L/→ +10sn,
M sessiz, F tam ekran, ↑↓ ses, 0–9 yüzde, Esc tam ekrandan çık. Sayfada
biri play deyince diğer `VideoPlayer` örnekleri pause.

Kalite değişimi `currentTime` ve play/pause korur; `error` olursa önceki
`src` + zamana döner, kısa satır: "Bu kalite oynatılamadı." Oynarken kuyruk
bitince `src` kendiliğinden değişmez; menü dolar. `localStorage` anahtarları
global: `player:volume`, `player:muted`, `player:rate`, `player:quality`.
Hatırlanan kalite bu dosyada yoksa `src` (orijinal) kullanılır.

`status=processing` iken 5 sn'de bir `status_url`. Üç başarısız deneme
poll'u durdurur; oynatma kesilmez. `ready` olunca config uygulanır, poll
biter. `failed` olunca "Hazırlanıyor" kalkar, menü yok.

`<video>` `error`: overlay "Video oynatılamadı." Orijinal URL indirme
linki olarak kalır.

### JS dosyaları

`public/js/video-player/` (iki dünyanın dışında, ES module, jQuery yok):

| Dosya | İşi |
|---|---|
| `player.js` | `VideoPlayer` — play, seek, ses, kalite, hız, tam ekran, PiP, klavye, poll, tek oynatma |
| `sprite-preview.js` | VTT + sprite hover |
| `prefs.js` | localStorage |
| `format.js` | `0:05`, `1:02:03` |

Boot: ön yüz `public/assets/js/video-player.js`, admin
`public/admin/assets/js/core/video-player.js`. İkisi `/js/video-player/player.js`
import eder, `[data-player]:not([data-player-ready])` tarar, `data-player-ready`
koyar. Bileşen `@once` ile script basar (video-field kalıbı).

### CSS

`public/assets/css/video-player.css` ve
`public/admin/assets/css/video-player.css`. Paylaşılmaz.

Sinema kromu: alt gradient `transparent → rgba(0,0,0,.75)`, ikon beyaz.
İlerleme: ön yüz `#155FFF` (`--vtc-bg-main4` / şerit), admin `#605DFF`
(Trezo primary-500). YouTube kırmızısı yok. Kutu 16:9, letterbox siyah.
Köşe: ön yüz ~12px, admin `rounded-md`. Oynatılmamışken alt çubuk yok;
ortada marka renkli dairesel play + poster (yoksa siyah zemin). Tıklanınca
kaynak yüklenir, çubuk gelir. Yüklemede spinner. ±10 sn merkez rozeti ~600 ms.
Ayar paneli dişliden yukarı. Süre çubuğu 4px / hover 6px. Tipografi: ön yüz
Outfit, admin Trezo; süre `tabular-nums` 12–13px. Mobil tap ≥44px; dar
ekranda ses slider gizlenir, mute kalır. `:focus-visible` 2px beyaz halka.

## Sağlık

`SystemHealth` içine `ffmpeg` kontrolü. `config/health.php` > `groups.server.keys`
listesine `ffmpeg` eklenir. `ffmpeg -version` (config yolu) çıkış 0 ise ok;
değilse critical, ipucu: sunucuya ffmpeg kurulumu ve `FFMPEG_PATH`.
Oynatma ffmpeg'siz de çalışır; menü/sprite boş kalır. Kuyruk işçisi yoksa
mevcut kalp atışı uyarısı yeter, ikinci mekanizma yok.

## Hata sözleşmesi

Orijinal durduğu sürece oynatma bozulmaz. Türetme yan kanaldır.

| Durum | Sonuç |
|---|---|
| ffmpeg yok | `failed`, orijinal oynar, sağlık kırmızı |
| Bozuk dosya | `failed`, "Video okunamadı." |
| Timeout | `failed`, "Video işlenirken zaman doldu." |
| Sprite düştü, kopyalar oldu | `ready`, `sprite` null, hover zaman |
| Kayıt iş dururken silindi | iş no-op |
| Poll 3 kez düştü | poll durur, sayfa yenilemesi düzeltir |
| Kalite src error | önceki src + zaman |
| Tam ekran / PiP yok | düğme gizli |
| Tanınmayan gömme | bileşen basılmaz (form zaten uyarıyor) |

## Dosyalar

Oluşturulacak / değişecek başlıcalar:

```
database/migrations/2026_09_14_000000_add_video_json_to_media_table.php
config/video.php
config/health.php
config/activity-log.php
app/Models/Media/Media.php
app/Services/Media/MediaService.php
app/Services/Media/VideoProcessor.php
app/Services/Health/SystemHealth.php
app/Jobs/ProcessVideoJob.php
app/Console/Commands/ProcessVideosCommand.php
app/Http/Controllers/Media/PlayerController.php
routes/web.php
resources/views/components/player.blade.php
resources/views/admin/components/player.blade.php
resources/views/admin/components/form/video.blade.php
resources/views/pages/projects/show.blade.php
public/js/video-player/{player,sprite-preview,prefs,format}.js
public/assets/js/video-player.js
public/assets/css/video-player.css
public/admin/assets/js/core/video-player.js
public/admin/assets/js/core/video-field.js
public/admin/assets/js/core/media-preview.js
public/admin/assets/css/video-player.css
resources/views/layout/partials/css.blade.php   (stack zaten var; bileşen @push)
CLAUDE.md                                       (kurulu altyapı satırı)
docs/superpowers/specs/2026-09-12-projects-frontend-design.md  (video satırı)
```

Controller ince kalır. `VideoProcessor` tek `process()`; blok gerçekten
uzun olduğu için probe / encode / sprite private bölünebilir (paylaşılmayan
uzun blok kuralına uygun). Yeni Composer paketi yok.

## Doğrulama

Otomatik test yazılmaz.

- [ ] `ffmpeg -version` ve `ffprobe -version` (Herd + hedef sunucu)
- [ ] Sistem sağlığı ffmpeg satırı: kuruluysa yeşil, değilse kırmızı
- [ ] `php artisan queue:work` açık
- [ ] Yeni mp4 yükle: özel oynatıcı, orijinal hemen, kalite "Hazırlanıyor",
      iş bitince 1080 kaynakta 1080/720/480, hover kare
- [ ] Kaynak 720: menüde 1080 yok, 720 ikinci kez yok
- [ ] `php artisan video:process` eski dosyayı kuyruğa alır; `ready` atlanır;
      `failed` yeniden girer
- [ ] Play/pause, ±10, sürükle, ses/mute, hız, kalite (zaman korunur),
      Space/J/L/M/F, çift tık sol/sağ/orta, PiP, tam ekran, ikinci oynatıcı durur
- [ ] Proje detayı gömme: iframe, özel çubuk yok; dosya: `<x-player>`
- [ ] Admin form 16:9 kullanılabilir çubuk; medya popup videoda oynatıcı
- [ ] Kayıt silinince rendition/poster/sprite diskte kalmaz
- [ ] `GET /media/{id}/player` video ise 200 JSON, değilse 404
- [ ] `vendor/bin/pint` temiz; ana sayfa / blog / hizmetler / iletişim /
      admin medya bozulmamış
