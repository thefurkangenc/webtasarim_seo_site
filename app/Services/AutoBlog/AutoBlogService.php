<?php

namespace App\Services\AutoBlog;

use App\Models\Ai\AiProvider;
use App\Models\Blog\Blog;
use App\Models\BlogCategory\BlogCategory;
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
        $system = $this->systemPrompt();
        $user = $this->userPrompt($topic);

        $article = $this->ask($provider, $config, [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ]);

        if (blank($article['title'] ?? null) || blank($article['content'] ?? null)) {
            throw new DomainException('Model başlık ya da içerik döndürmedi.');
        }

        $words = $this->wordCount((string) $article['content']);
        $floor = (int) $config['min_words'];

        if ($words >= $floor) {
            return $article;
        }

        /*
         * Hedef uzunluğu promptta istemek tek başına yetmiyor; model düzenli
         * olarak altında kalıyor. Yazıyı baştan üretmek yerine kendi metnini
         * derinleştirmesini istiyoruz — konu, başlık ve bölüm sırası korunur,
         * yalnızca içi dolar. Tek tur: ikinci bir turun getirisi masrafını
         * karşılamıyor ve sonuç dolgu cümleye kaçıyor. Turdan sonra hâlâ
         * kısaysa kaydetmiyoruz — 500 kelimelik taslak yayınlanacak bir yazı
         * değildir.
         */
        $expanded = $this->ask($provider, $config, [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
            ['role' => 'assistant', 'content' => json_encode($article, JSON_UNESCAPED_UNICODE)],
            ['role' => 'user', 'content' => "Bu gövde yalnızca {$words} kelime; hedef {$config['words']} kelime, "
                ."alt sınır {$floor}. Başlığı, konuyu ve bölüm sırasını AYNEN koru. Bölümleri örnek "
                .'senaryolar, adım adım anlatım, karşılaştırmalar ve uygulanabilir ayrıntılarla '
                .'derinleştir; gerekiyorsa yeni bölüm ekle. Var olan cümleleri tekrar etme, dolgu '
                .'cümle yazma. Tüm JSON alanlarını eksiksiz ve yeniden döndür.'],
        ]);

        $expandedWords = $this->wordCount((string) ($expanded['content'] ?? ''));

        if ($expandedWords < $floor) {
            throw new DomainException(
                "Gövde çok kısa kaldı ({$expandedWords} kelime, hedef {$config['words']}).",
            );
        }

        return $expanded;
    }

    /**
     * Tek bir sohbet isteği ve JSON çözümü. İki yerden çağrıldığı için ayrı
     * durur: ilk üretim ve genişletme turu aynı parametreleri kullanmalı.
     *
     * @param  array<string, mixed>  $config
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    private function ask(AiProvider $provider, array $config, array $messages): array
    {
        $response = $this->call($provider, 'chat/completions', $config['timeout'], [
            'model' => $config['model'],
            'max_completion_tokens' => $config['max_tokens'],
            'reasoning_effort' => $config['reasoning_effort'],
            'response_format' => ['type' => 'json_object'],
            'messages' => $messages,
        ]);

        $content = $response['choices'][0]['message']['content'] ?? null;

        if (! is_string($content) || trim($content) === '') {
            // Bütçe muhakemeye gittiyse finish_reason 'length' döner; boş
            // yanıtın nedenini bilmek ayar hatasını tahminden çıkarır.
            $reason = $response['choices'][0]['finish_reason'] ?? 'bilinmiyor';

            throw new DomainException("Model boş yanıt döndürdü (bitiş nedeni: {$reason}).");
        }

        return $this->decode($content);
    }

    /** Uzunluk denetimi gövde metnine bakar; HTML etiketleri kelime değildir. */
    private function wordCount(string $html): int
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags($html)));

        return $text === '' ? 0 : count(explode(' ', $text));
    }

    private function systemPrompt(): string
    {
        $words = config('auto-blog.text.words');
        $floor = (int) config('auto-blog.text.min_words');
        $high = (int) $words;
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', (string) $words, $match)) {
            $high = (int) $match[2];
        }
        $sections = 7;
        $perSection = (int) ceil(($high - 150) / $sections);
        $links = $this->links();

        $linkList = $links === []
            ? '(Şu an bağlanabilecek bir hizmet sayfası yok; hiç iç bağlantı verme.)'
            : collect($links)->map(fn (array $link) => "- {$link['anchor']} => {$link['path']}")->implode("\n");

        $categories = $this->categories();
        $categoryList = $categories === []
            ? '(Aktif kategori yok; blog_category_id alanını null bırak.)'
            : collect($categories)->map(fn (array $category) => "- {$category['id']}: {$category['name']}")->implode("\n");

        $tags = $this->allowedTags();
        $tagList = $tags === []
            ? '(Etiket listesi boş; tags alanını boş dizi bırak.)'
            : collect($tags)->map(fn (string $tag) => "- {$tag}")->implode("\n");

        return <<<PROMPT
        Sen bir web tasarım ve dijital çözümler ajansının kıdemli içerik editörü ve SEO içerik stratejistisin.
        Web tasarım, kurumsal web siteleri, web tasarım,özel yazılım, SEO, Google Ads, e-ticaret ve Google Maps İşletme Kaydı gibi konularda uzmansın.
        Yazılarını teknik bir okura değil, işini geliştirmek isteyen işletme sahiplerine ve yöneticilere yazarsın.

        Amacın; insanların gerçekten merak edebileceği konuları kendin belirleyerek, bu konular hakkında faydalı, anlaşılır, özgün ve SEO açısından güçlü blog içerikleri üretmektir.
        Blog yazıları doğrudan reklam metni gibi değil, okuyucunun sorusuna gerçekten cevap veren profesyonel içerikler gibi hazırlanmalıdır.

        Çoğu çalıştırmada sana konu, başlık veya anahtar kelime VERİLMEZ; böyle bir durumda konuyu tamamen kendin belirlemelisin. Konu seçerken web sitesinin hizmet alanlarını, hedef kitlesini ve daha önce yayınlanmış içerikleri dikkate almalısın.
        Kullanıcı mesajında bir konu, başlık ya da anahtar kelime verilmişse ona uy; o durumda kendi konunu seçme.
        Şema:
        {
          "search_query": "insanların Google'da arayabileceği doğal arama sorgusu",
          "service": "seçilen hizmetin adı",
          "blog_category_id": 3,
          "title": "blog başlığı",
          "excerpt": "kısa özet",
          "content": "HTML gövde",
          "tags": [],
          "focus_keyword": "tek odak ifade",
          "meta_title": "arama sonucu başlığı",
          "meta_description": "arama sonucu açıklaması",
          "meta_keywords": "virgülle ayrılmış ifadeler",
          "image_title": "görsel üzerinde kullanılacak blog başlığı",
          "image_subtitle": "gerekirse kısa açıklama",
          "image_prompt": "İngilizce görsel üretim talimatı"
        }
        Her çalıştırmada yeni ve yayınlanmaya değer bir blog konusu seç.
        Konuyu belirlerken kendine şu soruyu sor:
        Bir işletme sahibi veya işletmesinin dijital işlerini yaptırmak isteyen birisi bu konuyu  Google'da arar mı?"
        Cevabının olumlu ise devam et.
        Gerçek bir ihtiyaca cevap vermeyen, yalnızca SEO için oluşturulmuş yapay konular seçme.
        Konular; işletmelerin karşılaştığı problemler, merak ettiği sorular, hizmet satın almadan önce araştırdığı konular,
        karar verirken yaptığı karşılaştırmalar, maliyet ve süreç merakları, sık yapılan hatalar ve uygulanabilir çözüm önerileri üzerinden oluşturulabilir.

        Web tasarım, Web yazılım, Kurumsal Web Sitesi, özel yazılım, SEO, Google Ads, e-ticaret ve Google Maps İşletme Kaydı alanları arasında çeşitlilik oluştur.
        Kullanıcı mesajındaki geçmiş yazı listesine bak: son yazılar hangi hizmetin ve kategorinin
        etrafında toplanmışsa bu çalışmada o hizmeti ve o kategoriyi SEÇME, arşivde en az yer alan
        hizmete geç. Aynı hizmetin üst üste iki yazıda işlenmesi kabul edilemez.
        Son 4 yazıdan birisinin kategorisi web tasarım değilse web tasarım kategorisi seç.

        Her konu doğrudan bir hizmet satmak zorunda değildir.
        Ancak seçilen konu, verilen hizmetlerden en fazla biriyle doğal ve anlamlı şekilde ilişkilendirilebilmelidir.
        Daha önce yayınlanmış başlıkları ve konuları mutlaka dikkate al.

        Aynı konuyu yalnızca farklı bir başlıkla tekrar etme.
        Örneğin daha önce “Web Sitesi Yaptırmak Ne Kadar Sürer?” konusu işlendi ise “Kurumsal Web Sitesi Kaç Günde Hazırlanır?”
        gibi aynı arama niyetini tekrar ele alma.

        Yeni içerik blog arşivine gerçekten yeni bir konu veya yeni bir bakış açısı kazandırmalıdır.
        KÖTÜ KONU KALIPLARI. Hangi hizmetle ilgili olursa olsun bu kalıpları seçme:
        - "... nedir" tipi ansiklopedik tanım yazıları,
        - yıl içeren trend derlemeleri ("2026 ... trendleri"),
        - "...'in önemi", "...'in faydaları" gibi genel öneme dair yazılar,
        - kullanılan teknoloji, araç, framework veya yöntem listeleri,
        - hedef kitlesi işletme sahibi değil yazılımcı olan teknik anlatımlar,
        - hizmetin kendisini tanıtan, aslında bir hizmet sayfası olması gereken yazılar.

        Bunlar yerine gerçek bir ihtiyaca veya soruya odaklan. Aşağıda her hizmetten
        birer örnek var; listedeki hizmetlerin HEPSİ eşit derecede uygundur, biri
        diğerinden daha değerli değildir:
        - Web Tasarım: "Web Sitesi Yenilemeye Başlamadan Önce Elinizde Ne Hazır Olmalı?"
        - Özel Yazılım Geliştirme: "Hazır Panel mi Size Özel Yazılım mı? Hangisi Ne Zaman Mantıklı?"
        - SEO Danışmanlığı: "Sitem Google'da Neden İkinci Sayfada Kalıyor?"
        - e-Ticaret Yönetim Hizmeti: "Ürün Sayfasında Satışı Düşüren En Sık Hatalar"
        - Google İşletme Kaydı: "Bir İşletmenin Google Haritalar'daki Konumu Neden Yanlış Çıkar?"
        - Google Ads Reklam Yönetimi: "Google Reklamlarında Tıklama Başına Ücreti Ne Belirler?"

        Başlıklar bunlarla sınırlı değildir. Sadece daha iyi anlaman için veriyorum. Her çalışmada konuya en uygun başlığı kendin oluştur.

        İÇERİK:
        Gövde {$words} kelime OLMAK ZORUNDADIR (HTML etiketleri sayılmaz). {$floor} kelimenin
        altı reddedilir. Uzunluğu dolgu cümleyle, aynı şeyi farklı kelimelerle tekrar ederek
        veya girişi şişirerek değil, gerçek bilgiyle karşıla: örnek senaryolar, adım adım
        anlatım, karşılaştırmalar, dikkat edilecek noktalar, sık yapılan hatalar ve
        uygulanabilir öneriler.

        Bu uzunluğa matematik olarak şöyle ulaş: giriş yaklaşık 150 kelime, ardından en az
        {$sections} H2 bölümü ve her H2 altında en az {$perSection} kelime. Bölümü tek
        paragrafla geçiştirme. Alt başlıkları konuya göre kendin oluştur; her yazıda aynı
        başlık sırasını kullanma. On dört ince bölüm açma — {$sections} dolu bölüm, on dört
        boş bölümden iyidir.

        İçerikte gerektiğinde örnekler, karşılaştırmalar, kontrol listeleri, adımlar, dikkat edilmesi gereken noktalar ve sık yapılan hatalar kullan.
        İçerikte fiyat bilgisi verme.
        Ancak bunları sırf içerik uzunluğu oluşturmak için ekleme.
        Her içerik okuyucuya somut bir bilgi veya bakış açısı kazandırmalı.
        Teknik bir terim kullanılması gerekiyorsa anlaşılır şekilde açıkla.
        İçeriği geliştirici veya yazılımcı seviyesinde teknik bir makaleye dönüştürme.

        SATIŞ DİLİ:
        Blogun temel amacı bilgi vermektir.
        İlgili hizmete doğal bir geçiş yapılabilir ancak içerik reklam metnine dönüştürülmemelidir.
        Seçilen konu ile hizmet arasında gerçek bir bağlantı varsa hizmet sayfasına doğal bir iç bağlantı ver.

        Seçilen service yalnızca aşağıdaki listeden biri olabilir:
        Hizmetler: {$linkList}
        Seçilen hizmetin bağlantısını içerikte doğal bir cümle içerisinde kullan.
        En az bir, en fazla iki iç bağlantı kullan.
        Yalnızca seçilen hizmetin adresini kullan. Adres uydurma.

        KATEGORİ:
        blog_category_id, konuya EN UYGUN kategorinin id'si olmalıdır.
        Yalnızca aşağıdaki listeden birini seç. Yeni kategori uydurma.
        Kategoriler:
        {$categoryList}

        ETİKET:
        tags dizisi YALNIZCA aşağıdaki listeden seçilir. Yeni etiket uydurma,
        yazımı değiştirme, birleşik veya kısaltılmış versiyon üretme.
        Sayı serbesttir: gerçekten uyanları seç. Biri uyuyorsa 1, birkaçı uyuyorsa
        3-4, hiçbiri uymuyorsa boş dizi. Uydurmak için etiket ekleme.
        Etiketler:
        {$tagList}

        SEO:
        search_query alanında gerçek bir kullanıcının Google'a yazabileceği doğal bir sorgu oluştur.
        focus_keyword, search_query'nin ana ifadesi olmalı.
        Focus keyword başlıkta, giriş bölümünde, en az bir alt başlıkta ve meta description içerisinde doğal şekilde kullanılmalıdır.
        Anahtar kelimeyi gereksiz şekilde tekrar etme.
        Anahtar kelimenin farklı biçimlerini, yakın anlamlı ifadeleri ve konuya ilişkin semantik terimleri doğal şekilde kullan.
        SEO uğruna cümlelerin doğallığını bozma.
        meta_title en fazla 60 karakter olmalıdır.
        meta_description en fazla 150-160 karakter olmalı ve yazı içeriğinden bahsetmelidir.
        meta_keywordsyalnızca kısa SEO anahtar kelimelerinden oluşmalıdır. Toplam 4-6 adet keyword üret ve her keyword en fazla 2 kelime içersin. Keywordler cümle, soru veya uzun arama sorgusu şeklinde olmamalıdır. Keywordleriçeriğin konusunu, anahtar kelimeleri, kısa arama terimlerini veya hizmetini ifade etmelidir. 2 kelimeden uzun olan ifadeleri kesinlikle kullanma.
        excerpt en fazla 200 karakter olmalıdır.

        GERÇEKLİK VE UYDURMA YASAĞI:
        Gerçek olmayan istatistik, araştırma sonucu, fiyat, tarih, müşteri yorumu, referans, başarı oranı, şirket bilgisi veya uzman görüşü uydurma.
        Maliyet konusu işleniyorsa doğrulanmamış rakamlar verme. Bunun yerine maliyeti etkileyen faktörleri açıkla.
        Kesin sonuç veya garanti verme.
        Rakip firma isimleri verme.

        GÖRSEL ÜRETİMİ:
        Her blog yazısı için ayrıca bir kapak görseli üretilecek.
        Görsel, blog yazısının konusunu doğrudan anlatan profesyonel bir kapak görseli olmalıdır.
        Görsel üretiminde aşağıdaki referans görsel anlayışını temel al:
        Modern ve kurumsal bir tasarım.
        Çoğunlukla Açık ağırlıklı arka plan.
        Güçlü bir ana görsel. Kontrollü tipografi.
        image_title alanındaki başlık görsel üzerinde mutlaka yer almalıdır.
        Blog başlığını değiştirme veya farklı bir sloganla değiştirme.
        Başlık çok uzunsa anlamını koruyarak daha kısa ve tasarıma uygun bir versiyon oluşturabilirsin.
        Başlığın görseldeki en önemli ve en belirgin metin olmasını sağla.
        image_subtitle yalnızca gerçekten gerekiyorsa kullanılmalıdır.
        Görseli açıklama metinleriyle doldurma; zenginlik ikonlardan, rozetlerden,
        kartlardan ve dekoratif arayüz parçalarından gelir.

        GÖRSELİN TASARIMI:
        Görselin ana fikri tek bakışta anlaşılmalıdır.
        Gerçek fotoğrafik sahne olmayacak.
        Kapağın düzeni, renkleri ve tipografisi sistem tarafında sabittir; senin işin o
        iskeleti KONUYA özgü doğru nesne ve doğru ikonlarla doldurmaktır.

        Görselde firma adı, ajans adı veya marka adı kullanma.
        Görsel üzerinde blog başlığı ve gerekiyorsa kısa subtitle dışında rastgele metinler oluşturma.
        Görselde sahte istatistikler, sahte müşteri yorumları, sahte fiyatlar veya gerçekmiş gibi görünen sayısal sonuçlar gösterme.
        image_prompt İNGİLİZCE yazılmalıdır.
        Kapağın DÜZENİ, renkleri, tipografisi ve dekoratif parçaları (yüzen kartlar, kırmızı ok, el yazısı not)
        sistem tarafında zaten sabitlenmiştir. Bunları image_prompt içinde tekrar tarif etme, aksi halde
        çelişki çıkar. image_prompt YALNIZCA konuya özel içeriği anlatır:

        - ANA GÖRSEL: konuyu en iyi anlatan TEK nesne. Her konuda dizüstü bilgisayar kullanma;
          konuya göre havada duran bir tarayıcı penceresi, bir telefon, konum işaretli bir harita,
          bir mağaza arayüzü, bir belge/fatura sayfası, birkaç kargo kolisi ya da bir cihaz artı
          bir-iki gerçek nesne olabilir. Örnek: Google İşletme Kaydı konusunda harita, e-ticaret
          konusunda telefon ve koliler, SEO konusunda arama sonuçları penceresi.
        - O nesnenin üzerinde/ekranında ne görünüyor (hangi tür arayüz, hangi tür grafik, hangi tür liste),
        - yüzen kartların üzerinde hangi kısa ifadeler ve ne tür ikonlar var. İkonlar konunun
          kendi sözlüğünden gelsin (kargo konusunda koli ve kamyon, harita konusunda konum
          iğnesi ve yıldız gibi); her kapakta grafik-insan-ok üçlüsünü tekrar etme,
        - el yazısı notun ne söylediği.

        İki-üç cümle yeter. Arayüz metinlerinin okunabilir olmasını isteme.
        image_prompt, görselde kullanılacak Türkçe metni kendisi üretmemelidir. Görselde kullanılacak ana başlık image_title alanından alınacaktır.
        image_title en fazla 6 kelime olmalıdır; uzun başlıklar görselde bozuk basılır.

        Görsel, bir blog kapağı olduğu ilk bakışta anlaşılabilecek kadar düzenli ve profesyonel, Kurumsal ve estetik olmalıdır.

        SON KONTROL:
        - JSON'u oluşturmadan önce aşağıdakileri kontrol et:
        - Konu daha önce işlenmiş mi?
        - Konu gerçek bir kullanıcı ihtiyacına dayanıyor mu?
        - Başlık konuyu doğru anlatıyor mu?
        - Gövdedeki kelimeleri say (HTML etiketleri hariç). {$floor} kelimenin altındaysa
          JSON'u DÖNDÜRME; bölümleri genişlet, gerekiyorsa yeni bölüm ekle ve yeniden say.
        - İçerik başlığın vaat ettiği bilgiyi gerçekten veriyor mu?
        - Aynı cümle veya anlatım kalıpları tekrar edilmiş mi?
        - Anahtar kelimeler doğal mı?
        - Seçilen hizmet gerçekten konuyla ilgili mi?
        - blog_category_id listedeki kategorilerden biri mi ve konuya en uygun olanı mı?
        - tags yalnızca verilen etiket listesinden mi? Uymayanı eklemek için uydurma yok mu? Hiç uymuyorsa boş dizi mi?
        - İç bağlantı yalnızca seçilen hizmete mi gidiyor?
        - Görsel gerçekten blogun konusunu anlatıyor mu?
        - image_title blog başlığıyla uyumlu mu?
        - image_prompt görseli estetik, profesyonel, kurumsal ve konuya özgü üretmeye yeterince açık mı?
        - Herhangi bir gerçek dışı bilgi veya iddia var mı?
        Düzenlenmesi gereken alanlar varsa düzenle ve tekrar kontrol et.
        Tüm kontrollerden sonra yalnızca geçerli JSON çıktısını döndür.
        PROMPT;
    }

    private function userPrompt(array $topic): string
    {
        $history = $this->history();
        $words = config('auto-blog.text.words');
        $floor = (int) config('auto-blog.text.min_words');

        $lines = [];

        if ($topic['keywords'] !== '') {
            $lines[] = "Bu yazının konusu: {$topic['keywords']}";
            $lines[] = 'Bu anahtar kelimeleri metin içinde doğal biçimde geçir.';
        }

        if ($topic['title'] !== '') {
            $lines[] = "Başlık verildi, aynen kullan: {$topic['title']}";
        }

        if ($topic['notes'] !== '') {
            $lines[] = "Ek notlar: {$topic['notes']}";
        }

        if ($lines === []) {
            $lines[] = <<<'BRIEF'
            Konuyu sen belirle. Önce bir işletme sahibinin Google'a gerçekten
            yazacağı, hizmetlerden birine bağlanan ve aşağıdaki listede olmayan
            bir arama sorgusu bul; sonra o sorguya cevap veren yazıyı üret.
            BRIEF;
        }

        $brief = implode("\n", $lines);

        $categories = $this->categories();
        $categoryList = $categories === []
            ? '(Aktif kategori yok; blog_category_id alanını null bırak.)'
            : collect($categories)->map(fn (array $category) => "- {$category['id']}: {$category['name']}")->implode("\n");

        $tags = $this->allowedTags();
        $tagList = $tags === []
            ? '(Etiket listesi boş; tags alanını boş dizi bırak.)'
            : collect($tags)->map(fn (string $tag) => "- {$tag}")->implode("\n");

        return <<<PROMPT
        {$brief}

        Kategoriler. Konu için en uygun olanın id'sini blog_category_id olarak yaz;
        listede olmayan bir kategori uydurma:
        {$categoryList}

        Etiketler. Zorunlu değil. Yalnızca gerçekten uyanları, listedeki haliyle yaz;
        uymuyorsa tags'i boş dizi bırak, listede olmayan etiket yazma:
        {$tagList}

        Gövde {$words} kelime olacak (HTML etiketleri sayılmaz). {$floor} kelimenin altı kabul edilmez.
        Doğrudan JSON'u döndür.

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
     * Kapağın TASARIMI burada tanımlıdır: düzen, renk, tipografi ve
     * "mobilya" (yüzen kartlar, kırmızı ok, el yazısı not). Böylece her kapak
     * aynı aileden çıkar. Modelden gelen `image_prompt` yalnızca konuya özel
     * içeriği söyler — ekranda ne göründüğü ve kartların ne yazdığı.
     *
     *
     * Kompozisyonun birkaç ekseni her çağrıda rastgele değişir. Modele
     * "çeşitlilik kur" demek yetmiyor: sabit prompt aynı favori düzeni
     * üretiyor ve kapaklar birbirinin kopyası çıkıyor. Marka iskeleti
     * (solda metin, sağda tek nesne) sabit kalır; değişen şey açı, zemin
     * deseni ve dekor bileşimidir.
     */
    private function imagePrompt(array $article): string
    {
        $scene = trim((string) ($article['image_prompt'] ?? ''));
        $scene = $scene !== ''
            ? $scene
            : 'Main visual: a floating browser window showing a neutral business dashboard with '
            .'a sidebar, three KPI cards and a rising line chart. The floating cards carry a '
            .'generic icon each.';

        $title = trim((string) ($article['image_title'] ?? '')) ?: $article['title'];
        $subtitle = trim(strip_tags((string) ($article['excerpt'] ?? '')));
        if ($subtitle === '') {
            $subtitle = trim((string) ($article['image_subtitle'] ?? ''));
        }

        // Referanslardaki üst etiket hizmet adıdır; model onu `service` alanında
        // zaten veriyor, buraya kadar hiç kullanılmıyordu.
        $eyebrow = mb_strtoupper(trim((string) ($article['service'] ?? '')) ?: 'DİJİTAL ÇÖZÜMLER', 'UTF-8');

        $subtitleLine = $subtitle === ''
            ? 'No subtitle line under the headline.'
            : "A single-line subtitle under the headline, medium grey, regular weight,\n"
            ."   exactly as written (Turkish): \"{$subtitle}\"";

        $angle = Arr::random([
            'seen straight from the front, parallel to the canvas',
            'turned slightly to the left in a three-quarter view',
            'turned slightly to the right in a three-quarter view',
            'seen from a slightly raised angle and tilted back a little',
            'floating face-on with a slight perspective tilt',
            'seen from slightly below, so it feels tall and close',
        ]);

        $backdrop = Arr::random([
            'two large soft blurred blobs in pale pink and pale blue behind the hero object',
            'one wide pale blue circle behind the hero object and a small pale pink one near the headline',
            'a faint dotted grid behind the hero object, fading out towards the edges',
            'a soft diagonal band of very light grey crossing behind the hero object',
            'concentric very light grey rings radiating out from behind the hero object',
            'a plain clean background with no pattern at all, only a soft shadow under the hero object',
        ]);

        // 3:1 şeritte dekor sayısı azdır; kalabalık kompozisyon bu yükseklikte
        // okunmuyor. İkon satırı seçeneği bu yüzden havuzda yok.
        $decor = Arr::random([
            'two floating cards no arrow',
            'one floating card, two small badge pills and one curved red arrow',
            'two floating cards and one handwritten note, no arrow',
            'two floating cards stacked on one side only, no arrow',
            'one large floating card, one badge pill and one handwritten note',
            'three small badge pills and one curved red arrow, no cards',
        ]);

        return <<<PROMPT
        A polished blog cover graphic for a corporate web design and digital solutions
        agency. Flat vector interface design combined with ONE realistic hero object —
        an editorial marketing graphic, NOT a photograph of a room or a desk.

        CANVAS
        An ultra-wide banner, 3:1 — three times wider than it is tall. This is a short strip,
        so the composition is built sideways, not stacked: everything sits in one horizontal
        band and must fit without crowding. Background white to very light grey (#FFFFFF to
        #F7F8FA), with {$backdrop}. Generous whitespace, calm and uncluttered. Keep a
        comfortable margin on all four edges — nothing touches the frame or bleeds off it.

        LEFT SIDE, about 40% of the width, a compact block centred vertically in the strip:
        1. An eyebrow label in small uppercase letters with wide letter spacing, muted grey,
           followed by a short horizontal red dash, exactly as written: "{$eyebrow}"
        2. The headline, exactly as written (Turkish): "{$title}"
           Extra-bold geometric sans-serif of the Outfit or Poppins family, deep navy
           #0A1B3D, large, AT MOST TWO lines, with ONE key word or phrase coloured
           red #E8232A. The canvas is short, so keep the headline to two tight lines and
           still make it the biggest thing on the strip.
        3. {$subtitleLine}
        4. A solid red #E8232A rounded rectangle button with the short white label
           "Teklif Alın" and a white right-pointing arrow icon. Keep it small; it sits
           directly under the text, not far below it.

        RIGHT SIDE, about 60% of the width:
        ONE clean hero object that belongs to this specific topic — it is named at the bottom
        under MAIN VISUAL, use exactly that object. Do NOT fall back to a laptop: a laptop is
        only right when the subject really is building or running a website or a web app.
        Otherwise it can be a floating browser window, a smartphone, a map view with a
        location pin, a storefront interface, a document or an invoice sheet, a small group
        of parcels, a search field, a form, or one device with one or two real supporting
        objects. Render it as a crisp product-shot mockup {$angle}, with a soft shadow.
        Any interface text inside it stays tiny and reduced to soft grey placeholder bars,
        never readable words.

        ENRICHMENT — this is what makes the cover look designed, do not leave it out.
        For this particular cover use exactly: {$decor}.
        - Floating cards are white, rounded, with soft drop shadows, overlapping the edges of
          the hero object; each holds one small coloured rounded-square icon and one or two
          very short lines of text.
        - The curved red arrow is hand-drawn and points towards the hero object.
        - The handwritten note is a short script line in dark navy near a corner.
        - Badge pills are small rounded capsules with an icon and one or two words.
        Every icon must come from THIS topic's own vocabulary — pick symbols that only make
        sense for this subject. Do not reuse a generic set of chart, people and arrow icons
        on every cover. Vary where the cards sit rather than always stacking them the same way.

        COLOUR
        Deep navy #0A1B3D for text and dark surfaces, red #E8232A as the single accent,
        white cards, light grey #F1F3F7 surfaces, a touch of blue #2E6BE6 and one warm
        accent inside the charts. Muted and controlled, no neon, no full-canvas gradient.

        TEXT RULES
        Spell every Turkish character exactly: ç ğ ı İ ö ş ü. The headline is the largest
        and most prominent text on the canvas. Apart from the eyebrow label, the headline,
        the subtitle, the button label, the short card lines and the handwritten note there
        is NO other text. No paragraphs, no fake statistics, no fake testimonials, no
        prices, no company or brand names, no logos, no watermarks.

        AVOID
        Photographic office or desk scenes, stock-photo people, cartoon or sketchy
        illustration style, heavy 3D render look, isometric rooms, neon or cyberpunk
        lighting, holograms, collage, split screen, visual clutter, distorted hands or
        faces, garbled or misspelled text.

        MAIN VISUAL AND CARD CONTENT FOR THIS TOPIC
        {$scene}
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
                'blog_category_id' => $this->resolveCategoryId($article) ?? ($defaults['blog_category_id'] ?: null),
                'user_id' => $defaults['author_id'] ?: User::orderBy('id')->value('id'),
                'title' => $article['title'],
                'slug' => Slug::unique($article['title'], 'blogs'),
                'excerpt' => Str::limit(strip_tags((string) ($article['excerpt'] ?? '')), 480, ''),
                'content' => $article['content'],
                'status' => $status,
                'published_at' => $status === Blog::STATUS_PUBLISHED ? now() : null,
            ]);

            $blog->syncMedia($coverId, 'cover');
            $blog->syncTags($this->resolveTags($article));
            $blog->syncSeo([
                'meta_title' => $article['meta_title'] ?? null,
                'meta_description' => $article['meta_description'] ?? null,
                'meta_keywords' => $article['meta_keywords'] ?? null,
                'focus_keyword' => ($article['focus_keyword'] ?? null) ?: ($topic['keywords'] !== '' ? $topic['keywords'] : null),
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
     * Aktif blog kategorileri. Model yalnızca bu listeden seçer; uydurma
     * id kayda yazılmaz.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function categories(): array
    {
        return BlogCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name'])
            ->map(fn (BlogCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }

    /** Modelin verdiği id veya ad listede yoksa null — uydurma kategori kayda girmez. */
    private function resolveCategoryId(array $article): ?int
    {
        $categories = collect($this->categories());
        $raw = $article['blog_category_id'] ?? $article['category'] ?? null;

        if (is_numeric($raw)) {
            $match = $categories->firstWhere('id', (int) $raw);
            if ($match) {
                return (int) $match['id'];
            }
        }

        $name = mb_strtolower(trim((string) $raw), 'UTF-8');
        if ($name === '') {
            return null;
        }

        $match = $categories->first(
            fn (array $category) => mb_strtolower($category['name'], 'UTF-8') === $name,
        );

        return $match['id'] ?? null;
    }

    /** @return list<string> */
    private function allowedTags(): array
    {
        return array_values(array_filter(array_map(
            fn ($tag) => trim((string) $tag),
            Arr::wrap(config('auto-blog.tags', [])),
        )));
    }

    /**
     * Model uydurursa kayda girmez. Eşleşme büyük/küçük harf duyarsız,
     * yazım config'teki kanonik ada çekilir.
     *
     * @return list<string>
     */
    private function resolveTags(array $article): array
    {
        $lookup = [];
        foreach ($this->allowedTags() as $tag) {
            $lookup[mb_strtolower($tag, 'UTF-8')] = $tag;
        }

        $chosen = [];
        foreach (Arr::wrap($article['tags'] ?? []) as $tag) {
            $key = mb_strtolower(trim((string) $tag), 'UTF-8');
            if ($key !== '' && isset($lookup[$key])) {
                $chosen[$key] = $lookup[$key];
            }
        }

        return array_values($chosen);
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
