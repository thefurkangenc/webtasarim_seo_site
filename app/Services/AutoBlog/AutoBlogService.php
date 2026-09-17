<?php

namespace App\Services\AutoBlog;

use App\Models\Ai\AiProvider;
use App\Models\Blog\Blog;
use App\Models\Service\Service;
use App\Models\User;
use App\Services\Media\MediaService;
use App\Support\Slug;
use DomainException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Cron'dan tetiklenen blog üretimi: yazı ve kapak görseli tek yerde.
 *
 * Bilinçli olarak AiService/BlogService'ten bağımsızdır — o hat paneldeki
 * elle üretim içindir (prompt kayıtları, kuyruk, durum sorgulama). Burada
 * tek bir düz akış vardır ve her iki prompt da bu sınıfın içindedir.
 *
 * Tek istisna MediaService'tir: görsel medya kütüphanesine kaydedilmeli,
 * WebP/kırpma/türev üretimi orada tek yerde durur, kopyalanmaz.
 *
 * Adımlar: sağlayıcı bul -> konu seç -> yazıyı üret -> kapağı üret ->
 * taslak kaydı aç.
 */
class AutoBlogService
{
    public function __construct(private readonly MediaService $media) {}

    public function generate(array $input = []): Blog
    {
        $provider = $this->provider();
        $topic = $this->topic($input);
        $article = $this->write($provider, $topic);

        // Aynı başlık iki kez üretilirse ikinci kayıt açılmaz: konu havuzu
        // rastgele seçtiği için tekrar mümkündür, kopya içerik SEO'ya zarar verir.
        if (Blog::where('title', $article['title'])->exists()) {
            throw new DomainException("Bu başlıkla bir yazı zaten var: {$article['title']}");
        }

        return $this->store($article, $topic, $this->cover($provider, $article));
    }

    /*
    |--------------------------------------------------------------------------
    | Sağlayıcı ve konu
    |--------------------------------------------------------------------------
    */

    /** Panelde tanımlı ChatGPT kaydı — yalnızca adres ve anahtar için. */
    private function provider(): AiProvider
    {
        /** @var AiProvider|null $provider */
        $provider = AiProvider::query()
            ->where('driver', 'openai')
            ->where('is_active', true)
            ->when(config('auto-blog.provider_id'), fn ($query, $id) => $query->whereKey($id))
            ->orderByDesc('is_default')
            ->first();

        if (! $provider || blank($provider->api_key)) {
            throw new DomainException(
                'Aktif bir ChatGPT sağlayıcısı bulunamadı. Yapay Zeka > Sağlayıcılar ekranından anahtarı girin.',
            );
        }

        return $provider;
    }

    /**
     * Konu normalde modelin kendi seçimidir; üçü de boş dönebilir. Adrese
     * ?keywords=... verilirse o brief kazanır (elle deneme ve tek seferlik
     * istekler için).
     *
     * @return array{keywords: string, title: string, notes: string}
     */
    private function topic(array $input): array
    {
        return [
            'keywords' => trim((string) ($input['keywords'] ?? '')),
            'title' => trim((string) ($input['title'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Yazı
    |--------------------------------------------------------------------------
    */

    /** @return array<string, mixed> */
    private function write(AiProvider $provider, array $topic): array
    {
        $config = config('auto-blog.text');

        $response = $this->call($provider, 'chat/completions', $config['timeout'], [
            'model' => $config['model'],
            'temperature' => $config['temperature'],
            'max_completion_tokens' => $config['max_tokens'],
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $this->userPrompt($topic)],
            ],
        ]);

        $content = $response['choices'][0]['message']['content'] ?? null;

        if (! is_string($content) || trim($content) === '') {
            throw new DomainException('Model boş yanıt döndürdü.');
        }

        $article = $this->decode($content);

        if (blank($article['title'] ?? null) || blank($article['content'] ?? null)) {
            throw new DomainException('Model başlık ya da içerik döndürmedi.');
        }

        return $article;
    }

    private function systemPrompt(): string
    {
        $words = config('auto-blog.text.words');
        $links = $this->links();

        $linkList = $links === []
            ? '(Şu an bağlanabilecek bir hizmet sayfası yok; hiç iç bağlantı verme.)'
            : collect($links)->map(fn (array $link) => "- {$link['anchor']} => {$link['path']}")->implode("\n");

        return <<<PROMPT
        Sen bir web tasarım ve dijital çözümler ajansının kıdemli içerik editörü
        ve SEO içerik stratejistisin. Web tasarım, kurumsal web siteleri, özel
        yazılım, SEO, Google Ads, e-ticaret ve Google İşletme konularında
        uzmansın; ama yazılarını teknik bir okura değil, işini büyütmek isteyen
        sıradan bir işletme sahibine yazarsın.

        Amaç: İnsanların Google'a gerçekten yazdığı sorulara net cevap veren,
        okuyanın işine yarayan ve onu ajansın ilgili hizmetine doğal biçimde
        yönlendiren blog yazıları üretmek. Okuyucu küçük bir atölyenin sahibi
        de olabilir, bir nakliye firmasının patronu da, bir holdingin pazarlama
        müdürü de. Hepsi aynı yazıyı okuyup anlayabilmeli.

        Ajansın hizmetleri (her yazı bunlardan TAM OLARAK BİRİNE bağlanır):
        {$linkList}

        Yanıtını YALNIZCA geçerli bir JSON nesnesi olarak ver. JSON dışında
        açıklama, selamlama ya da kod çiti yazma. Alanları bu sırayla doldur;
        önce arama sorgusunu ve hizmeti belirle, yazıyı ona göre kur.

        Şema:
        {
          "search_query": "insanların Google'a yazacağı haliyle arama sorgusu",
          "service": "yukarıdaki listeden bu sorgunun bağlandığı hizmetin adı",
          "title": "yazının başlığı",
          "excerpt": "tek paragraf özet",
          "content": "HTML gövde",
          "tags": ["etiket", "etiket"],
          "focus_keyword": "tek odak ifadesi",
          "meta_title": "arama sonucu başlığı",
          "meta_description": "arama sonucu açıklaması",
          "meta_keywords": "virgülle ayrılmış ifadeler",
          "image_title": "kapak görselinde yazacak başlık",
          "image_subtitle": "kapakta başlığı destekleyen kısa açıklama ya da boş metin",
          "image_prompt": "İngilizce kapak sahnesi tarifi"
        }

        KONU SEÇİMİ — en önemli kısım:

        1. Önce search_query'yi bul. Bir işletme sahibinin aklına takılıp
           Google'a yazdığı, günlük dilde bir sorgu olmalı. Kendine sor:
           "Bunu gerçekten biri arama kutusuna yazar mı, ayda birçok kişi
           yazar mı?" Cevap "pek sanmam" ise başka sorgu bul.
        2. Sorgu, yukarıdaki hizmetlerden birinin satın alma yolculuğunda
           bir yere oturmalı: sorunu fark etme ("müşteriler beni Google'da
           bulamıyor"), seçenekleri araştırma ("hazır site mi yazılım mı"),
           karar verme ("web sitesi yaptırırken nelere dikkat edilir"),
           süreç ve maliyet merakı ("web sitesi kaç günde hazır olur",
           "Google reklamı pahalı mı"). Listede olmayan bir hizmete
           (e-posta pazarlama, sosyal medya yönetimi, UX araştırması gibi)
           dayanan konu SEÇME.
        3. İyi sorgu kalıpları: "... neden ...", "... mı yoksa ... mı",
           "... nasıl yapılır", "... işe yarar mı", "... gerekli mi",
           "... ne kadar sürer", "... fiyatını ne belirler", "... yaparken
           yapılan hatalar", "... için ne gerekir", "... hangisi daha iyi".
           Bunlar kalıptır, her yazıda farklısını kullan.
        4. KÖTÜ konular — bunları seçme:
           - Ders kitabı başlıkları: "X Nedir ve Önemi", "Dijital Dönüşümde
             X'in Rolü", "Etkili X Stratejileri ile Y'yi Artırın".
           - Kimsenin aramadığı soyut kavramlar: "kullanıcı deneyimi
             felsefesi", "dijital dönüşüm yolculuğu", "marka bilinci".
           - Trend/yıl yazıları, yapay zeka üzerine genel yorumlar.
           - Yalnızca geliştiricinin anlayacağı teknik konular (framework,
             sunucu mimarisi, kod).
        5. Başlık, search_query'nin okunaklı bir halidir. Sorgudaki ana
           ifadeyi içerir, soruyu ya da vaadi açıkça söyler, clickbait değildir.
           Örnek dönüşüm: "web sitesi yaptırmak kaç gün sürer" →
           "Web Sitesi Yaptırmak Kaç Gün Sürer? Süreyi Uzatan 6 Etken".

        Yazmadan önce kontrol et; biri bile olumsuzsa konuyu değiştir:
        - Bu konu (ya da aynı sorunun başka türlü sorulmuş hali) kullanıcı
          mesajındaki listede var mı?
        - Teknik bilgisi olmayan bir işletme sahibi bu başlığa tıklar mı?
        - Yazı, o kişinin aklındaki soruyu gerçekten cevaplıyor mu?
        - Konu listedeki bir hizmete doğal biçimde bağlanıyor mu?

        İÇERİK — zengin, anlaşılır, teknik makale değil:
        - Hedef uzunluk {$words} kelime. Bunu tutturmak için gövdeyi 6-8 adet
          <h2> bölümüne böl; her bölüm en az 2-3 dolu paragraf ya da paragraf +
          liste olsun (bölüm başına kabaca 200-250 kelime). Tek cümlelik ya da
          yalnızca madde işaretinden oluşan bölüm yazma.
        - Giriş paragrafı soruya ilk 2-3 cümlede doğrudan cevap versin (Google
          öne çıkan snippet'i buradan alır), sonra yazının neleri anlatacağını
          söylesin.
        - Günlük konuşma diline yakın, sade Türkçe yaz; "siz" diye hitap et.
          Teknik bir terim geçmek zorundaysa aynı cümlede ne anlama geldiğini
          açıkla.
        - Soyut anlatma, somutlaştır: farklı ölçekte işletmelerden kısa,
          gerçekçi senaryolar kur ("küçük bir mobilya atölyesi...", "şehirler
          arası çalışan bir nakliye firması...", "birden çok şirketi olan bir
          grup..."). Bunlar varsayımsal örnektir, gerçek firma ya da müşteri
          gibi sunma.
        - Okuyucunun yapabileceği pratik şeyler ver: kontrol listesi, dikkat
          edilecek işaretler, sorulması gereken sorular, adım adım yol.
        - Alt başlıklar da soru ya da somut ifade olsun ("Süre en çok nerede
          uzar?"). "... Nedir?", "Önemi", "Sonuç" gibi boş başlıklar kullanma.
        - Sondaki bölüm özet değil, okuyucunun bir sonraki adımıdır: kendi
          durumunu nasıl değerlendireceği ve ne zaman profesyonel destek
          alması gerektiği. Satış diline kaçma.
        - Son bölümden önce <h2>Sık Sorulan Sorular</h2> altında aynı konuda
          insanların sorabileceği 3-4 soruyu <h3> olarak yaz, her birine 2-4
          cümlelik net cevap ver.
        - Her yazıyı aynı cümleyle başlatma; "Dijital dünyada", "Günümüzde",
          "Günümüzün hızla değişen" gibi klişe açılışlar kullanma. Cümle ve
          paragraf uzunluklarını çeşitlendir.
        - Kesin sonuç ya da garanti veren ifadeler kullanma.

        Uydurma yasağı: gerçek olmayan istatistik, araştırma sonucu, fiyat,
        tarih, müşteri yorumu, referans, başarı oranı, şirket bilgisi ya da
        uzman görüşü yazma. Maliyet sorulan konularda rakam verme; fiyatı neyin
        belirlediğini anlat. Rakip firma adı verme.

        Hizmete yönlendirme:
        - "service" alanında seçtiğin hizmetin adresine metin içinde en az bir,
          en fazla iki kez <a href="...">...</a> ile bağlantı ver; bağlantı
          cümlenin doğal parçası olsun ("Google İşletme kaydınızı biz de
          sizin için yönetebiliriz" gibi zorlama değil).
        - Yalnızca yukarıdaki listedeki adresleri kullan, adres uydurma. Başka
          bir hizmet de gerçekten ilgiliyse ona da bir bağlantı verilebilir.

        SEO:
        - focus_keyword search_query'nin çekirdek ifadesidir; başlıkta, giriş
          paragrafında, en az bir <h2>'de ve meta açıklamada doğal biçimde geçsin.
          Konuyla ilgili yan ifadeleri ve eş anlamlıları metne yay; aynı
          kelimeyi zorla tekrar etme.
        - meta_title en fazla 60 karakter, meta_description 150-160 karakter ve
          okuyucuya ne öğreneceğini söylesin.
        - meta_keywords 5-8 ifade, tags 3-6 kısa etiket.
        - excerpt en fazla 200 karakter, düz metin.

        HTML biçimi:
        - content yalnızca şu etiketleri kullanır: <h2>, <h3>, <p>, <ul>, <ol>,
          <li>, <strong>, <em>, <blockquote>, <a>. <h1>, <script>, <style> ve
          satır içi style kullanma.

        Kapak görseli alanları:
        - image_title: kapakta yazacak başlık. Yazının başlığıdır; çok uzunsa
          anlamı bozulmadan kısalt (en fazla 60 karakter). Türkçe karakterleri
          doğru yaz.
        - image_subtitle: başlığı destekleyen en fazla 90 karakterlik tek cümle.
          Yalnızca görselin konuyu daha iyi anlatmasına katkı sağlıyorsa yaz;
          sağlamıyorsa boş metin ("") bırak.
        - image_prompt: İngilizce, 1-3 cümle. Konuyu doğrudan yansıtan gerçekçi ve
          profesyonel bir sahne anlat: çalışma ortamı, cihaz, ekran, arayüz ya da
          konuya özgü unsurlar. Kompozisyonu konuya göre sen kurgula. Sahnede
          başlık dışında yazı, firma ya da marka adı olmasın (başlıkta geçen bir
          ürün adı konunun parçasıysa kullanılabilir).
        PROMPT;
    }

    private function userPrompt(array $topic): string
    {
        $history = $this->history();

        $brief = $topic['keywords'] === ''
            ? <<<'BRIEF'
            Konuyu sen belirle. Önce bir işletme sahibinin Google'a gerçekten
            yazacağı, hizmetlerden birine bağlanan ve aşağıdaki listede olmayan
            bir arama sorgusu bul; sonra o sorguya cevap veren yazıyı üret.
            Doğrudan JSON'u döndür.
            BRIEF
            : <<<BRIEF
            Bu yazının konusu: {$topic['keywords']}
            Bu anahtar kelimeleri metin içinde doğal biçimde geçir.
            BRIEF;

        if ($topic['title'] !== '') {
            $brief .= "\nBaşlık verildi, aynen kullan: {$topic['title']}";
        }

        if ($topic['notes'] !== '') {
            $brief .= "\nEk notlar: {$topic['notes']}";
        }

        return <<<PROMPT
        {$brief}

        Sitede hâlihazırda bulunan yazılar (taslaklar dahil, en yeniden eskiye).
        Bu konuları, eş anlamlılarını ve aynı sorunun farklı kelimelerle sorulmuş
        hallerini tekrar etme; aynı odak kelimeyi de yeniden hedefleme:
        {$history}
        PROMPT;

    }

    /*
    |--------------------------------------------------------------------------
    | Kapak görseli
    |--------------------------------------------------------------------------
    */

    /** Görseli üretir, medya kütüphanesine kaydeder, media id döner. */
    private function cover(AiProvider $provider, array $article): ?int
    {
        $config = config('auto-blog.image');

        if (! ($config['enabled'] ?? false)) {
            return null;
        }

        $response = $this->call($provider, 'images/generations', $config['timeout'], [
            'model' => $config['model'],
            'prompt' => $this->imagePrompt($article),
            'size' => $config['size'],
            'quality' => $config['quality'],
            'n' => 1,
        ]);

        // gpt-image ailesi base64 döner; eski dall-e uçları url döndürüyordu.
        $encoded = $response['data'][0]['b64_json'] ?? null;
        $binary = is_string($encoded) ? base64_decode($encoded, true) : false;

        if ($binary === false || $binary === '') {
            throw new DomainException('Görsel üretildi ama okunamadı.');
        }

        $path = sys_get_temp_dir().'/'.Str::uuid()->toString().'.png';
        file_put_contents($path, $binary);

        try {
            $media = $this->media->store(
                new UploadedFile($path, Str::slug($article['title']).'.png', 'image/png', null, true),
                [
                    'preset' => 'blog.cover',
                    'accept' => 'image',
                    'name' => $article['title'],
                    'alt' => $article['title'],
                ],
            );
        } finally {
            // storeAs geçici dosyayı taşır; hata durumunda arkada kalmasın.
            if (is_file($path)) {
                unlink($path);
            }
        }

        return $media->id;
    }

    /**
     * Modelin verdiği sahneyi ve kapak başlığını sabit bir tasarım diliyle
     * sarar. Stil tarifi burada durur ki her kapak hizmet görselleriyle aynı
     * aileden çıksın.
     *
     * Üretim 1536x1024, `blog.cover` preset'i 2:1'e kırpar — üstten ve alttan
     * yaklaşık %13 gider. Yazı bu yüzden dikey ortadaki güvenli alanda tutulur.
     */
    private function imagePrompt(array $article): string
    {
        $scene = trim((string) ($article['image_prompt'] ?? ''));
        $scene = $scene !== ''
            ? $scene
            : 'A tidy modern office desk with an open laptop showing a clean website layout, '
                .'a notebook and a cup of coffee, soft window light.';

        $title = trim((string) ($article['image_title'] ?? '')) ?: $article['title'];
        $subtitle = trim((string) ($article['image_subtitle'] ?? ''));

        $text = $subtitle === ''
            ? "Headline text, exactly as written (Turkish): \"{$title}\"\nNo other text besides the headline."
            : "Headline text, exactly as written (Turkish): \"{$title}\"\n"
                ."Smaller supporting line under the headline, exactly as written (Turkish): \"{$subtitle}\"\n"
                .'No other text besides these two.';

        return <<<PROMPT
        Blog cover design for a corporate web design and digital solutions agency.

        {$text}

        Typography: the headline is the most prominent element, bold modern sans-serif,
        deep navy (#05051C), large and highly legible, broken into at most 3 balanced lines.
        Spell every Turkish character exactly (ç, ğ, ı, İ, ö, ş, ü). Keep all text inside the
        vertical middle 70% of the canvas with generous margins — the top and bottom edges
        will be cropped.

        Scene: {$scene}

        Style: clean and airy composition, white or light background, professional modern
        corporate aesthetic, realistic photographic elements, deep navy as the main colour
        with red or blue accents only where needed. Text on one side, the realistic scene on
        the other, landscape framing.

        Must not contain: company names, brand names or logos (unless part of the headline),
        watermarks, slogans, decorative text, extra labels, badges, cards, many icons,
        illustration or cartoon style, neon or cyberpunk lighting, holograms, collage,
        distorted hands or faces.
        PROMPT;
    }

    /*
    |--------------------------------------------------------------------------
    | Kayıt
    |--------------------------------------------------------------------------
    */

    private function store(array $article, array $topic, ?int $coverId): Blog
    {
        $defaults = config('auto-blog.defaults');
        $status = $defaults['status'] === Blog::STATUS_PUBLISHED
            ? Blog::STATUS_PUBLISHED
            : Blog::STATUS_DRAFT;

        return DB::transaction(function () use ($article, $topic, $coverId, $defaults, $status) {
            $blog = Blog::create([
                'blog_category_id' => $defaults['blog_category_id'] ?: null,
                'user_id' => $defaults['author_id'] ?: User::orderBy('id')->value('id'),
                'title' => $article['title'],
                'slug' => Slug::unique($article['title'], 'blogs'),
                'excerpt' => Str::limit(strip_tags((string) ($article['excerpt'] ?? '')), 480, ''),
                'content' => $article['content'],
                'status' => $status,
                'published_at' => $status === Blog::STATUS_PUBLISHED ? now() : null,
            ]);

            $blog->syncMedia($coverId, 'cover');
            $blog->syncTags(Arr::wrap($article['tags'] ?? []));
            $blog->syncSeo([
                'meta_title' => $article['meta_title'] ?? null,
                'meta_description' => $article['meta_description'] ?? null,
                'meta_keywords' => $article['meta_keywords'] ?? null,
                'focus_keyword' => ($article['focus_keyword'] ?? null) ?: $topic['keywords'],
                'og_media_id' => $coverId,
            ]);

            return $blog;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Yardımcılar
    |--------------------------------------------------------------------------
    */

    /** OpenAI'ye tek giriş noktası — metin ve görsel aynı anahtarı kullanır. */
    private function call(AiProvider $provider, string $endpoint, int $timeout, array $body): array
    {
        try {
            $response = Http::timeout($timeout)
                ->withToken((string) $provider->api_key)
                ->acceptJson()
                ->post(rtrim($provider->base_url, '/').'/'.$endpoint, $body);
        } catch (ConnectionException $exception) {
            throw new DomainException("OpenAI adresine ulaşılamadı: {$exception->getMessage()}");
        }

        if ($response->failed()) {
            throw new DomainException(
                $response->json('error.message') ?? "OpenAI isteği reddetti (HTTP {$response->status()}).",
            );
        }

        return (array) $response->json();
    }

    /**
     * Model JSON'u kod çitiyle sarabiliyor; ilk { ile son } arası okunur.
     *
     * @return array<string, mixed>
     */
    private function decode(string $raw): array
    {
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        $decoded = $start === false || $end === false
            ? null
            : json_decode(substr($raw, $start, $end - $start + 1), true);

        if (! is_array($decoded)) {
            throw new DomainException('Model geçerli JSON döndürmedi.');
        }

        return $decoded;
    }

    /**
     * Modele verilecek iç bağlantı listesi. Hizmet başlıkları bölge yer
     * tutucusu taşıyabildiği için ({{city}} gibi) temizlenir.
     *
     * @return array<int, array{anchor: string, path: string}>
     */
    private function links(): array
    {
        return Service::where('status', Service::STATUS_PUBLISHED)
            ->orderBy('sort_order')
            ->limit(12)
            ->get(['id', 'title', 'slug'])
            ->map(fn (Service $service) => [
                'anchor' => trim((string) preg_replace('/\{\{\s*\w+\s*\}\}/', '', $service->title)),
                'path' => '/hizmetler/'.$service->slug,
            ])
            ->filter(fn (array $link) => $link['anchor'] !== '')
            ->values()
            ->all();
    }

    /**
     * Mevcut arşiv. Konuyu model seçtiği için tekrarı engelleyen tek şey bu
     * listedir; taslaklar da dahildir, yoksa aynı konu üst üste üretilir.
     * Odak kelime varsa eklenir — hangi kelime alanının tutulduğunu başlıktan
     * daha net söyler.
     */
    private function history(): string
    {
        $blogs = Blog::with('seo')
            ->orderByDesc('id')
            ->limit((int) config('auto-blog.text.history', 200))
            ->get(['id', 'title']);

        if ($blogs->isEmpty()) {
            return '(henüz yazı yok, arşivi sen açıyorsun)';
        }

        return $blogs
            ->map(function (Blog $blog) {
                $keyword = $blog->seo?->focus_keyword;

                return $keyword ? "- {$blog->title} (odak: {$keyword})" : "- {$blog->title}";
            })
            ->implode("\n");
    }
}
