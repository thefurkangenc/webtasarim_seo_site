# Otomatik Blog Üretimi — Cron + ChatGPT API (Yazı ve Kapak)

Tarih: 2026-09-17
Durum: Araştırma / yönlendirme (kod yazılmadı)
İlgili: `2026-09-03-blog-seo-ai-design.md`

Bu belge bir uygulama planı değil. Cron ile blog yazısı ve kapak görseli üretmek
istendiğinde **mevcut altyapıya nasıl oturur, hangi modeller yeter, prompt nasıl
yazılır, Google’a zarar vermemek için ne yapılmaz** sorularına cevap verir.

Fiyatlar **17 Eylül 2026** itibarıyladır. OpenAI fiyatı değişir; uygulamaya
geçmeden resmi sayfayı tekrar bakın.

---

## 1. Amaç

Panelden elle yazı açmak yerine:

1. Belirli aralıklarla (cron) bir konu seçilsin.
2. ChatGPT API ile Türkçe, SEO uyumlu yazı üretilsin.
3. Aynı API ailesinden kapak görseli üretilsin — hizmet sayfalarındaki
   kurumsal fotoğraflara yakın, “AI illüstrasyonu” değil.
4. Kayıt **taslak** olarak düşsün; yayın insan kararı olsun.

Hedef: **en ucuz ve işi çözen** OpenAI modelleri. En iyi model değil.

---

## 2. Karar özeti (önce bunu okuyun)

| Konu | Tavsiye | Neden |
|---|---|---|
| Yazı modeli | `gpt-4o-mini` | Projede zaten varsayılan. JSON mode var. 800–1500 kelimelik TR yazı ~$0.002. |
| Yazı denemesi | `gpt-5-nano` veya `gpt-4.1-nano` | Daha ucuz olabilir. 10 yazılık A/B. JSON bozulursa kalınmaz. |
| Kapak modeli | `gpt-image-1-mini` | Medium + 1536×1024 ≈ **$0.015**. Kalite/fiyat dengesi. |
| Kapak kalitesi | `medium` (prodüksiyon), `low` (deneme) | High 3× pahalı; cron’da gerekmez. |
| Kapak boyutu | `1536×1024` yatay | Blog preset 1200×630; sonra `MediaService` kırpar. |
| Stil kopyası | Hizmet kapaklarından 1–2 referans foto | Prompt tek başına “hizmet görseli gibi” demez. Referans söyler. |
| Yayın | Her zaman `draft` | Google ölçekli AI içeriği spam sayıyor. |
| Hacim | Haftada **2–3** yazı | Günde 5–10 yazı siteyi zayıflatır, maliyeti değil kaliteyi bozar. |
| Mimari | Metin = mevcut chat API. Görsel = ayrı Images API | `ChatDriver` görsel üretmez. Karıştırmayın. |

Tek yazının maliyeti kabaca **$0.017**. Bunun ~%90’ı görseldir. Yazıyı
pahalı modele taşımak görselden daha az kazanç getirir.

---

## 3. Mevcut sistem — ne var, ne yok

### 3.1 Blog kaydı nasıl oluşuyor

Kaynaklar:

- Model: `app/Models/Blog/Blog.php`
- Servis: `app/Services/Blog/BlogService.php`
- Admin form: `resources/views/admin/pages/blog/form.blade.php`
- Medya preset: `config/media.php` → `blog.cover`

`BlogService::create()` bir transaction içinde kaydı açar, sonra ilişkileri
bağlar:

- kapak: `$blog->syncMedia($cover_media_id, 'cover')`
- etiket: `syncTags`
- SEO: `syncSeo` (skor `SeoAnalyzer` ile yazılır)
- SSS: `syncFaqs` (havuzdaki mevcut FAQ id’leri)

**Zorunlu alanlar:** `title`, `status`. İçerik, kapak, SEO, etiket, kategori
zorunlu değil. Cron bir yazıyı yarım bıraksa bile kayıt açılabilir; yine de
üretim işi hepsini doldurmalı.

Kapak `blogs` tablosunda kolon değil. Polymorphic `mediables` pivot. Yani
görseli önce medya kütüphanesine koymak, sonra `cover` koleksiyonuna bağlamak
zorunlu — paneldeki akışın aynısı.

Slug boşsa başlıktan üretilir (`Slug::unique`). Aynı başlık iki kez gelirse
çakışma olmaz ama içerik tekrar eder; konu kuyruğunda tekillik şart.

Yayınlanınca sitemap ve IndexNow gözlemcileri zaten çalışır. Taslakken
ön yüze düşmez (`publicUrl()` yalnızca `published`).

### 3.2 Medya hattı

`MediaService::store($file, ['preset' => 'blog.cover', 'alt' => ...])`:

1. Orijinali `uploads/{Y}/{m}/{uuid}-original.{ext}` olarak saklar.
2. Preset varsa **1200×630**’a kırpar.
3. Ana dosyayı WebP (kalite 85) yazar.
4. `thumb` 400×400 cover, `medium` en fazla 1000px scale üretir.

Cron’un yapması gereken: API’den gelen baytı geçici dosyaya yazmak, onu
`UploadedFile` gibi `store()`’a vermek, dönen `media.id`’yi `cover_media_id`
olarak `create()`’e koymak. Yeni bir görsel tablosu açmaya gerek yok.

| Preset | Boyut | Oran | Nerede |
|---|---|---|---|
| `service.cover` | 800×500 | 8:5 | Hizmet kartı / detay |
| `blog.cover` | 1200×630 | ~1.9:1 (OG) | Blog kapak, sosyal paylaşım |
| `seo.og` | 1200×630 | aynı | Ayrı OG istenirse |

Blog kartı CSS’i 16:10, detay 16:9. Preset OG. Üretilen ham görsel biraz daha
geniş (1536×1024) olsun; kırpma merkezden 1200×630 alsın. Dar kare üretmek
yanlış — kenarlar kesilir, kompozisyon bozulur.

### 3.3 Yapay zeka bugün ne yapıyor

Kaynaklar: `app/Services/Ai/AiService.php`, `config/ai.php`,
`database/seeders/AiPromptSeeder.php`.

- Sürücüler: OpenAI, DeepSeek, Ollama. Hepsi **sohbet**. Görsel uç noktası yok.
- OpenAI varsayılan model: **`gpt-4o-mini`**. `response_format: json_object`.
- Şablon anahtarı `blog.content`. Çıktı:

```json
{
  "title": "...",
  "excerpt": "...",
  "content": "<p>...</p>",
  "tags": ["...", "..."],
  "meta_description": "...",
  "meta_keywords": "..."
}
```

- Üretim kuyrukta (`GenerateContentJob`). `tries = 1` (ücretli çağrı tekrar
  edilmesin diye).
- Panel butonu formu doldurur, **kaydetmez**. İnsan Kaydet’e basar.

Cron bu hattı kullanabilir (aynı `AiService::dispatch/run`) ama kapak için
yeni bir çağrı şart: `POST /v1/images/generations` (veya Responses API image
tool). Bunu `OpenAiCompatibleDriver::chat()` içine gömmeyin. Metin sürücüsü
şişer, DeepSeek/Ollama kırılır, zaman aşımı karışır.

### 3.4 Cron bugün ne yapıyor

`bootstrap/app.php` içinde: sitemap (04:00), kırık link (pazartesi 05:00),
sağlık, haftalık rapor, cron kalp atışı. **AI veya blog üretimi yok.**

Sunucuda `* * * * * php artisan schedule:run` ve `queue:work` zaten bekleniyor
(AI üretimi ve sağlık paneli buna bağlı). Yeni iş aynı düzene eklenir:
komut zamanlanır, asıl iş kuyruğa düşer. Cron sürecinde OpenAI’yi senkron
beklemeyin — PHP-FPM/cron zaman aşımı + kuyruk işçisi yokken “üretildi sanma”.

### 3.5 Eksikler (bunlar yazılacak iş, bu belgede yok)

- Images API istemcisi
- Konu kuyruğu (ne yazılacağını kim seçer)
- Artisan komut + schedule satırı
- Taslağa yazan servis metodu (mevcut `create()` yeterli olabilir)
- Referans görsel seçimi (hangi hizmet kapakları stil kaynağı)
- Günlük maliyet tavanı / “bugün zaten üretildi” kilidi

---

## 4. Hizmet görselleri neden güzel duruyor?

Üç katman. AI yalnızca üçüncüyü taklit etmeye çalışır; ilk ikisi zaten sitede.

1. **Oran disiplini.** `service.cover` 8:5. Kart CSS’i `aspect-ratio: 8 / 5` +
   `object-fit: cover`. Kenar boşluğu, ezilme, rastgele boyut yok.
2. **Teknik çıktı.** WebP 85, net thumb/medium. Bulanık JPEG yok.
3. **Fotoğraf dili.** Kurumsal stok: gerçek mekân, doğal ışık, insan veya
   ekran/cihaz, sade kompozisyon. Neon siber, 3D render, “AI gradient blob”
   yok.

Blog kapakları da aynı dili konuşmalı. Fark yalnızca çerçeve: hizmet 8:5,
blog OG 1200×630. Konu da farklı olabilir (SEO grafiği, editör masası, laptop
üzerinde site, toplantı) ama **ışık, renk, gerçekçilik** hizmet kapaklarıyla
aynı aileden olmalı.

Lacivert marka rengi (`#05051C` / `#081120`) ortamda aksan olarak geçebilir
(ekran ışığı, defter, duvar, giysi). Görsele logo veya yazı basmayın — görsel
modeller yazıyı bozar.

**En güçlü yöntem:** Prompt’a “corporate stock photo” yazmak yetmez. Images
API görsel girdi kabul eder. 1–2 beğenilen hizmet kapağını referans verip
“aynı fotoğraf stili, farklı sahne, yazı yok” demek, stil sapmasını keser.

---

## 5. Önerilen mimari

```
Konu kuyruğu (panel veya config)
        │
        ▼
php artisan blog:generate   ← cron tetikler, iş kuralı yok denecek kadar ince
        │
        ▼
Kuyruk işi (ShouldBeUnique, günlük kilit)
        │
        ├─ 1. Metin: mevcut AiService + blog.content (genişletilmiş JSON)
        ├─ 2. Görsel: Images API (gpt-image-1-mini) + isteğe bağlı referans
        ├─ 3. MediaService::store(preset: blog.cover)
        └─ 4. BlogService::create(status: draft, cover_media_id, seo, tags)
        │
        ▼
Admin /blog  → insan okur, düzeltir, yayınlar
```

### 5.1 Neden iki API?

| | Chat Completions | Images |
|---|---|---|
| Ne üretir | JSON metin | PNG/WebP bayt |
| Model | `gpt-4o-mini` | `gpt-image-1-mini` |
| Projede var mı | Evet | Hayır |
| Maliyet | ~$0.002 | ~$0.015 |

Tek “ajan” modeline (gpt-5.6 + image tool) her şeyi yaptırmak mümkün ama
pahalı ve mevcut `ChatDriver` sözleşmesini bozar. Cron için ayrık iki çağrı
daha ucuz, daha tahmin edilebilir, hata ayıklaması kolay.

### 5.2 Konu kuyruğu

Modelin “bugün ne yazayım?” diye uydurması tekrar ve yamyum içerik üretir.
Konuyu insan seçsin, üretim otomatik olsun.

İyi konu: ajansın gerçek işi + arama niyeti.

- Gaziantep web tasarım fiyatını ne belirler
- Kurumsal site kaç günde yayınlanır
- Yerel SEO ile Google İşletme farkı
- Hizmet sayfasına neden bölge eklenir
- TinyMCE / panelden içerik yönetmek (kendi ürüne iç link)

Kötü konu: “2026’da dijital pazarlama trendleri” — her sitede aynı metin,
Google’ın “özgün değer yok” dediği sınıf.

Kuyrukta tutulması gerekenler: anahtar kelime, isteğe bağlı başlık, kategori
id, hedef hizmet slug’ı (iç link için), “kullanıldı / atlandı” durumu.

### 5.3 Yayın politikası (kırmızı çizgi)

İlk günden `published` yazmak cazip. Yapmayın.

- Google: otomasyon yasak değil; **asıl amacı sıralama manipülasyonu olan
  ölçekli, emeksiz içerik** spam.
- Kalite rater kılavuzu (2025/2026): ana içeriği az emekle üretilmiş AI
  metin + stok görsel sayfalar **Lowest**.
- Bu site bir ajans vitrini. İnce, birbirinin kopyası 40 yazı, 4 iyi
  yazıdan daha zararlı.

Taslak → 5 dakikalık editör geçişi (uydurma rakam, iç link, başlık, kapak
yüzü) → yayın. Cron “yazar” değil “asistan”.

### 5.4 Teknik kenarlar

- `ShouldBeUnique` + “bugün üretildi” kilidi. Cron çift tetiklenmesin.
- OpenAI hata verirse kayıt açmayın. Yarım blog + kapaksız taslak kirliliği.
- Başlık benzerliği: mevcut yayınlanmış `title`/`slug` ile kaba çakışma kontrolü.
- `user_id`: sistem kullanıcısı veya süper yönetici. `null` bırakılabilir
  (model izin veriyor) ama “Yazar” boş görünür.
- Kuyruk işçisi yoksa üretim durur — bu zaten AI panelinin kuralı.
- Görsel indirme zaman aşımı chat’ten uzun olabilir; iş timeout’unu Images
  tarafına göre ayarlayın (mevcut AI işi `providerTimeout + 30`).

---

## 6. Model seçimi ve maliyet (Eylül 2026)

Kaynak: [OpenAI API Pricing](https://openai.com/api/pricing/),
[GPT-4o mini](https://developers.openai.com/api/docs/models/gpt-4o-mini),
[GPT-Image-1 Mini](https://developers.openai.com/api/docs/models/gpt-image-1-mini),
[Image generation](https://developers.openai.com/api/docs/guides/image-generation).

### 6.1 Yazı (Chat Completions)

Fiyatlar 1 milyon token.

| Model | Girdi | Çıktı | Bu iş için |
|---|---|---|---|
| **gpt-4o-mini** | $0.15 | $0.60 | **Birincil.** JSON, Türkçe, mevcut sürücü. |
| gpt-5-nano | ~$0.05 | ~$0.40 | Daha ucuz aday. JSON/Türkçe A/B şart. |
| gpt-4.1-nano | $0.10 | $0.40 | Ucuz ve hızlı; uzun yazıda sığ kalabilir. |
| gpt-5.6-luna | $0.20 | $1.20 | Nano kuşağı, 4o-mini’den pahalı çıktı. Gerekmez. |
| gpt-5-mini | $0.25 | $2.00 | Çıktı pahalı. Blog gövdesi uzun olduğu için kaçının. |
| gpt-4o / gpt-5.6-terra | $2.50+ / $2.00 | $10 / $12 | Overkill. |

Kabaca bir yazı: ~600 token girdi (sistem + konu) + ~2.500–4.000 token çıktı
(HTML JSON). `gpt-4o-mini` ile **$0.002 civarı**. Ayda 12 yazı ≈ 3 cent.

`config/ai.php` zaten `gpt-4o-mini` diyor. Paneldeki sağlayıcı kaydında model
adı farklıysa cron o kaydı kullanır — cron için ayrı “ucuz sağlayıcı”
açmak karışıklık çıkarır. Aynı ChatGPT kaydı, aynı anahtar.

**json_object** kalsın. Serbest metin + “JSON çıkar” kırılgan. Mevcut
`AiService::parse()` zaten ilk `{` ile son `}` arasını kesiyor; yine de model
kod çiti basmasın diye şablondaki kural dursun.

### 6.2 Kapak (Images API)

`gpt-image-1-mini` resmi per-image tarifesi:

| Kalite | 1024×1024 | 1536×1024 veya 1024×1536 |
|---|---|---|
| low | $0.005 | $0.006 |
| **medium** | $0.011 | **$0.015** |
| high | $0.036 | $0.052 |

Prompt token’ı ayrı (metin girdi $2 / 1M). Referans görsel eklenirse görsel
girdi token’ı eklenir; tek referansta hâlâ sentler mertebesi.

Diğer görsel modeller:

| Model | Not |
|---|---|
| **gpt-image-1-mini** | Cron için doğru seçim. |
| gpt-image-1 | Eski hat; **23 Ekim 2026** civarı emekli planı var. Yeni işe koyulmasın. |
| gpt-image-1.5 | Daha iyi, medium ~$0.034. Mini yetmezse yedek. |
| gpt-image-2 / 2.5-flare / sunburst | Güncel amiral. Token bazlı, belirgin pahalı. Tek kahraman görsel için; günlük kapağa değil. |
| DALL·E 3 | ~$0.04–0.12, yazı ve prompt takibi daha zayıf. Yeni işe alınmasın. |
| DALL·E 2 | $0.02, kalite bu site için yetersiz. |

Boyut: **1536×1024 landscape**. Kare 1024 ucuz ama blog/OG yatay; kareyi
1200×630’a kırpmak yüzleri ve ekranları keser.

Kalite: deneme serisinde `low` ($0.006) ile prompt’u oturtun. Beğenilen
prompt’u `medium`’a sabitleyin. `high` yalnızca “bu kapak reklamda da
kullanılacak” derseniz.

### 6.3 Aylık örnek

Haftada 3 yazı, medium yatay kapak, `gpt-4o-mini`:

- Yazı: 12 × $0.002 ≈ $0.03
- Kapak: 12 × $0.015 ≈ $0.18
- Referans görsel token: ihmal
- **Toplam ≈ $0.21 / ay**

Maliyet sorunu değil. Sorun kalite ve Google. Bütçe “daha ucuz model”
değil, “daha az ve daha iyi yazı” ile yönetilir.

`gpt-image-2.5` high ile aynı hacim onlarca dolara çıkabilir. O modeli
seçmeyin diye bu belgede duruyor.

---

## 7. Prompt araştırması

İki prompt, iki dil. Metin Türkçe (site dili). Görsel prompt **İngilizce**
(görsel modeller İngilizce sahnede daha istikrarlı; Türkçe “kurumsal ofis”
bazen rastgele poster basar).

### 7.1 Metin — mevcut şablonu genişletin, yeniden icat etmeyin

`AiPromptSeeder` içindeki `blog.content` iyi bir temel: JSON only, HTML
etiket listesi, `<h1>` yasak, uydurma istatistik yasak, excerpt/meta uzunluk
sınırları. Cron için eklenecekler:

- `focus_keyword` (SEO skorlama bunu kullanıyor; panel AI’si şu an doldurmuyor)
- `image_prompt` (İngilizce, görsel çağrısına gidecek)
- `internal_links` (önerilen hizmet/blog URL’leri — model uydurmasın, listeden seçsin)

Sistem promptuna eklenecek gerçekler (placeholder ile):

- Kim konuşuyor: Gaziantep odaklı web tasarım / SEO ajansı, birinci çoğul.
- Kime: KOBİ sahibi, “ajans jargonu yeme”.
- Yapma: “dijital dünyada”, “günümüzün hızla değişen”, “başarıya giden yol”,
  yıl + “trendleri”, sahte müşteri hikâyesi, sahte yüzde.
- Yap: somut süreç, karar kriteri, “ne zaman bize gelin”, ilgili hizmete
  işaret. Konu Gaziantep ise zorla doldurmayın; doğal geçsin.
- `content`: `<p>` giriş + en az 3 `<h2>`. İstenen uzunluk 800–1200 kelime
  (ince yazı spam sinyali; 3000 kelime de dolgu olur).
- Verilen iç link listesinin dışına `href` uydurma.

Kullanıcı şablonu mevcut `{{keywords}} {{title}} {{category}} {{length}}
{{notes}}` ile kalabilir. Cron `notes` içine “hedef hizmet: /hizmetler/web-tasarim”
ve “yayımlanmış yazı başlıkları: …” basarak tekrarı keser.

JSON şema önerisi (mevcut alanlar + ekler):

```json
{
  "title": "arama niyetine uygun, tıklama yemi değil",
  "excerpt": "en fazla 200 karakter, düz metin",
  "content": "HTML gövde",
  "tags": ["3-6 kısa etiket"],
  "focus_keyword": "tek ifade",
  "meta_description": "150-160 karakter, odak kelime doğal",
  "meta_keywords": "5-8 ifade",
  "image_prompt": "English photographic prompt, no letters in the scene",
  "internal_links": [{"anchor": "...", "path": "/hizmetler/..."}]
}
```

`internal_links` modelin uydurduğu path’ler değil, cron’un verdiği listeden
seçim olmalı. Sistem promptu: “Yalnızca verilen path’leri kullan.”

İki aşamalı üretim (önce taslak, sonra yazı) kaliteyi artırır, maliyeti ve
gecikmeyi ikiye katlar. Haftada 3 yazıda `gpt-4o-mini` tek JSON yeter. Kalite
düşerse ikinci çağrı eklenir; baştan çift çağrı açmayın.

### 7.2 Görsel — sahne + yasak + stil, “güzel yap” değil

Görsel modeller genel övgüye (“professional, stunning, 8k”) boyun eğer ve
aynı stok klisesini basar. Sahneyi kilitleyin.

**İskelet:**

1. Tür: photorealistic photograph, not illustration, not 3D, not CGI.
2. Sahne: somut (ör. “small Turkish agency meeting room, one laptop showing
   a clean business website wireframe, natural window light, navy notebook”).
3. İnsan: varsa az, poz doğal, bakış kameraya kilitli değil. Yüz yoksa daha
   az “fake stock”.
4. Renk: muted, dark navy `#05051C` as accent only, no neon.
5. Kompozisyon: landscape 3:2, subject off-center, negative space on one
   side (kırpma 1200×630’dan sonra da çalışsın).
6. Yasak: **no text, letters, numbers, logos, watermarks, UI captions,
   browser chrome with readable URLs**, extra fingers, collages, split
   screens, glowing holograms.

`image_prompt` alanını metin modeli üretecekse ona aynı yasakları sistem
promptunda verin. Aksi halde her yazıda “laptop with the word SEO in giant
3D” gelir.

**Referans görsel cümlesi** (Images API image input ile):

> Match the lighting, color grading, depth of field and realism of the
> reference photos. New subject, same photographic family. Do not copy
> the reference scene.

Referans olarak en “fotoğraf” duran 2 hizmet kapağını seçin. İllüstratif
veya ikonik olanları seçmeyin — model onları stil sanır.

### 7.3 Örnek görsel prompt’lar (konuya göre)

Web tasarım:

> Photorealistic wide photo of a quiet studio desk: 16-inch laptop with a
> simple corporate website homepage on screen (no readable text, blurred
> type), a closed navy notebook, ceramic cup, soft daylight from the left.
> Shallow depth of field, muted colors, dark navy accent in the notebook.
> No logos, no letters, no watermark. Landscape composition.

SEO / Google:

> Photorealistic photograph of a person’s hands on a laptop trackpad,
> screen showing a generic analytics dashboard of charts only, no labels
> or numbers readable. Modern office, daylight, navy chair edge in
> foreground. Editorial stock, not CGI. Landscape, no text.

Ajans / ekip (yüz riski yüksek; dikkat):

> Photorealistic over-the-shoulder photo of two colleagues looking at a
> large monitor with a website layout, faces turned away or out of focus.
> Natural office light, realistic skin, no beauty-filter look. No text on
> screens. Landscape.

Yüz üretmek ucuz stok “gülümseyen ekip”e kayar. Tercihen ekran, el, mekân.

### 7.4 Prompt’u nasıl oturtursunuz (kod yok, süreç var)

1. Aynı 5 konu için `low` kalitede 2’şer görsel. Beğenilen cümleleri not edin.
2. Kazanan prompt + 1 referans foto ile `medium`.
3. 1200×630 kırpınca konu ortada kalıyor mu bakın. Kalmıyorsa “negative space
   on the right” ekleyin.
4. Metin JSON’unu 10 yazılık okuyun: klişe açılış, uydurma rakam, tekrar
   başlık. Şablonu sıkılaştırın, modeli büyütmeyin.
5. Ondan sonra cron’a bağlayın.

---

## 8. SEO — bunu yanlış yapmak görselden pahalıya patlar

Google (Search Central, 2023 ve sonrası tutarlı): AI ile içerik **yasak
değil**. Sıralamayı manipüle etmek için otomasyon **spam**.

Kalite rater kılavuzundaki ilgili fikir: ana içerik az emekle, az özgünlükle,
ziyaretçiye ek değer katmadan AI veya başka kaynaktan üretilmişse **Lowest**.
Ölçekli içerik suistimali (scaled content abuse) araç fark etmez.

Bu site için pratik kurallar:

- Haftada 2–3, her biri gerçek bir soruya cevap. Günde 5 “nedir?” yazısı yok.
- Yayın öncesi insan: başlık, ilk paragraf, iç link, sahte rakam tarama.
- Hizmet sayfalarına gerçek iç link. Blog birbirini ve vitrini beslesin.
- Kapak `alt` = yazı başlığı veya odak kelime, İngilizce prompt değil.
- Aynı iskeleti 20 kez doldurmayın. Konu kuyruğu çeşitlensin.
- Yazar adı gerçek kişi olsun (E-E-A-T). “AI Editor” diye imza atmayın;
  gizlemeyin de — asıl sinyal özgünlük.
- SSS: havuzdan rastgele bağlamak anlamsız. Ya üretmeyin ya yazıya özel
  3 soru (ayrı JSON alanı, `faqs` tablosuna yazmak ayrı iş). İlk fazda SSS
  atlanabilir.

IndexNow ve sitemap taslakta tetiklenmemeli; yayın `save` ile zaten gözlemciye
düşer.

---

## 9. Sonra uygulanırsa sıra (bu belge kod yazmaz)

1. **Prompt denemesi** — paneldeki mevcut AI + elle Images playground.
   Model ve prompt kilitlenmeden cron yok.
2. **Görsel istemcisi** — OpenAI Images, `gpt-image-1-mini`, geçici dosya →
   `MediaService::store`. Chat sürücüsüne dokunulmaz.
3. **Konu kuyruğu** — basit tablo veya `config` listesi. İlk sürümde config
   yeter; panel şart değil.
4. **Artisan + job + schedule** — günde en fazla 1, haftalık tavan 3.
   Çıktı daima taslak.
5. **Admin’de “Cron taslağı” filtresi** — `is_featured` ile karıştırmayın;
   gerekirse `source=ai-cron` gibi bir iz (yeni kolon tartışılır, şart değil:
   belirli `user_id` de yeter).
6. **Ölç** — 10 yayından sonra: tarama, gösterim, “ince içerik” şikâyeti.
   İşe yaramazsa hacmi düşürün veya kapatın; modeli büyütmek birinci çare değil.

Açık kararlar (uygulama turunda sorulacak):

- Konu listesini kim doldurur? (siz / sekreter / aylık brainstorm)
- Yazar kullanıcısı kim?
- Referans kapak olarak hangi 2 hizmet görseli?
- İlk fazda SSS üretilecek mi?
- Taslak kaç gün içinde yayınlanmazsa silinsin mi?

---

## 10. Yapılacaklar / yapılmayacaklar

**Yapın**

- Metni `gpt-4o-mini` + mevcut JSON kalıbıyla üretin.
- Kapağı `gpt-image-1-mini` medium 1536×1024 ile üretin.
- Hizmet kapaklarını stil referansı verin.
- `MediaService` + `BlogService::create` kullanın.
- Taslak bırakın, haftada 2–3 yazı.
- Görsel prompt’u İngilizce ve yasaklı yazın (no text).

**Yapmayın**

- `ChatDriver` içine DALL·E / Images gömmeyin.
- `gpt-image-1` (emekli) veya DALL·E 3 ile yeni hat açmayın.
- Her yazıyı `gpt-5.6-terra` + `gpt-image-2.5` ile basmayın.
- Otomatik `published` yapmayın.
- Modelden şehir, fiyat, vaka, yüzde uydurtmayın.
- Unsplash/Pexels rastgele stok (lisans + “her sitede aynı foto”) — referanslı
  üretim daha tutarlı; stok API bu projede yok, şart da değil.
- Günde 10 yazı ile “SEO domine” planı.

---

## 11. Kaynaklar

- Proje: `config/ai.php`, `config/media.php`, `app/Services/Blog/BlogService.php`,
  `app/Services/Ai/AiService.php`, `database/seeders/AiPromptSeeder.php`,
  `bootstrap/app.php`, `docs/superpowers/specs/2026-09-03-blog-seo-ai-design.md`
- OpenAI fiyat: https://openai.com/api/pricing/
- GPT-4o mini: https://developers.openai.com/api/docs/models/gpt-4o-mini
- GPT-Image-1 Mini: https://developers.openai.com/api/docs/models/gpt-image-1-mini
- Image generation: https://developers.openai.com/api/docs/guides/image-generation
- Google, AI içerik: https://developers.google.com/search/blog/2023/02/google-search-and-ai-content
- Spam politikası (scaled content): https://developers.google.com/search/docs/essentials/spam-policies
