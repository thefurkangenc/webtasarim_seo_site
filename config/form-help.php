<?php

/*
|--------------------------------------------------------------------------
| Form alanı yardım metinleri
|--------------------------------------------------------------------------
|
| <x-admin::form.help topic="schema.search_url" /> bileşeni buradan okur.
| Anahtar nokta notasyonludur: "grup.alan". Her giriş:
|
|   'alan' => [
|       'title' => 'Kısa başlık',
|       'body'  => '<p>Elle yazılmış GÜVENLİ HTML.</p>',   // {!! !!} ile basılır
|   ],
|
| Kural: teknik jargon yok. "JWT", "OAuth", "endpoint", "callback" gibi
| terimler yerine kullanıcının anlayacağı dille yazılır. Body içinde
| <p> <ul> <ol> <li> <strong> <b> <code> <a> <em> serbest; stil
| resources/css/admin/style.css içindeki .help-pop bloğundan gelir.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Ortak alanlar — birden çok modülde tekrar eden bileşenler
    |----------------------------------------------------------------------
    */
    'common' => [

        'slug' => [
            'title' => 'Kısa ad (slug)',
            'body' => <<<'HTML'
                <p>Bu içeriğin web adresinde görünen kısmı — örn. <code>siteniz.com/blog/<strong>web-tasarim-fiyatlari</strong></code>.</p>
                <ul>
                    <li>Yalnızca küçük harf, rakam ve tire kullanın; Türkçe karakterler otomatik çevrilir (ş→s, ı→i)</li>
                    <li>Kısa ve anlamlı tutun, odak anahtar kelimeyi içersin</li>
                    <li><strong>Boş bırakırsanız</strong> başlıktan otomatik üretilir — çoğu zaman en iyisi budur</li>
                </ul>
                <p>Yayınlanmış bir sayfanın kısa adını değiştirirseniz eski adres çalışmaz; sistem otomatik bir yönlendirme oluşturur ama yine de dikkatli olun.</p>
                HTML,
        ],

        'excerpt' => [
            'title' => 'Özet',
            'body' => <<<'HTML'
                <p>İçeriği bir-iki cümlede anlatan kısa metin. Şurada görünür:</p>
                <ul>
                    <li>Liste ve kart görünümlerinde başlığın altında</li>
                    <li>Meta açıklama boş bırakılırsa arama sonuçlarında</li>
                    <li>Sosyal medyada paylaşımda</li>
                </ul>
                <p>Reklam cümlesi değil, içeriğin ne sunduğunu net anlatan bir giriş yazın. 1–2 cümle yeterli.</p>
                HTML,
        ],

        'content' => [
            'title' => 'İçerik',
            'body' => <<<'HTML'
                <p>Sayfanın asıl gövdesi. Biçimlendirme araç çubuğuyla başlık, liste, bağlantı, görsel ekleyebilirsiniz.</p>
                <ul>
                    <li>Metni <strong>ara başlıklarla</strong> (H2, H3) bölün — hem okunur hem SEO'ya iyi gelir</li>
                    <li>Görsel eklerken alt metin (açıklama) yazın; görme engelliler ve Google için önemli</li>
                    <li>Başka sayfalarınıza bağlantı verin</li>
                </ul>
                <p>Karanlık moda geçerseniz editör içeriği koruyarak yeniden yüklenir.</p>
                HTML,
        ],

        'status' => [
            'title' => 'Durum',
            'body' => <<<'HTML'
                <p><strong>Taslak:</strong> yalnızca panelde görünür, sitede yayınlanmaz. Üzerinde çalışırken bunu seçin.</p>
                <p><strong>Yayında:</strong> herkese açık. Yayın tarihi ileri bir tarihse, o tarih gelene kadar yine görünmez.</p>
                HTML,
        ],

        'published_at' => [
            'title' => 'Yayın tarihi',
            'body' => <<<'HTML'
                <p>İçeriğin sitede görünmeye başlayacağı tarih ve saat.</p>
                <ul>
                    <li><strong>Boş bırakılırsa</strong> kaydettiğiniz an yayınlanır</li>
                    <li><strong>İleri bir tarih</strong> girerseniz durum “Yayında” olsa bile o ana kadar sitede görünmez (zamanlanmış yayın)</li>
                    <li>Geçmiş bir tarih, sıralamada içeriği daha eski gösterir</li>
                </ul>
                HTML,
        ],

        'tags' => [
            'title' => 'Etiketler',
            'body' => <<<'HTML'
                <p>İçeriği konularına göre gruplayan kısa kelimeler. Ziyaretçi bir etikete tıklayınca o etiketi taşıyan tüm içerikler listelenir.</p>
                <ul>
                    <li>Yazıp <strong>Enter</strong> ya da virgül ile ekleyin</li>
                    <li>Listede olmayan etiket otomatik oluşturulur</li>
                    <li>“Web Tasarım” ile “web tasarım” aynı etikettir — büyük/küçük harf farkı önemsiz</li>
                    <li>İçerik başına 3–6 etiket idealdir; her şeyi etiketlemeyin</li>
                </ul>
                HTML,
        ],

        'faqs' => [
            'title' => 'Sıkça sorulan sorular',
            'body' => <<<'HTML'
                <p>Bu içeriğe bağlı soru-cevaplar. Sayfanın altında açılır liste olarak gösterilir ve <strong>Google'da genişletilmiş sonuç</strong> (soruların doğrudan arama sonucunda çıkması) için işaretlenir.</p>
                <p>Sorular ayrı bir havuzda tutulur; “Soru Seç” ile mevcut sorulardan seçersiniz. Aynı soru birden çok sayfaya bağlanabilir. Yeni soru eklemek için <em>SSS</em> modülünü kullanın.</p>
                HTML,
        ],

        'cover_media' => [
            'title' => 'Kapak görseli',
            'body' => <<<'HTML'
                <p>İçeriğin ana görseli. Liste kartlarında, sayfanın üstünde ve (paylaşım görseli boşsa) sosyal medya paylaşımında kullanılır.</p>
                <ul>
                    <li>Yatay, net ve konuyla ilgili bir görsel seçin</li>
                    <li>Seçince kırpma penceresi açılır — önerilen orana kilitlidir</li>
                    <li>Kütüphaneden daha önce yüklediğiniz bir görseli de seçebilirsiniz</li>
                </ul>
                HTML,
        ],

        'map' => [
            'title' => 'Harita konumu',
            'body' => <<<'HTML'
                <p>İşletmenizin harita üzerindeki noktası. İletişim sayfasındaki haritayı ve yapılandırılmış veriyi (Google'ın işletmenizi haritada göstermesi) besler.</p>
                <p>Haritaya tıklayın ya da işareti sürükleyin; enlem/boylam kutuları kendiliğinden dolar. Harita açılmıyorsa <strong>Entegrasyonlar</strong> sekmesinden Google Maps'i etkinleştirin veya koordinatları elle yazın (Google Haritalar'da bir yere sağ tıklayınca koordinat çıkar).</p>
                HTML,
        ],

        'active' => [
            'title' => 'Aktif',
            'body' => <<<'HTML'
                <p>Kapalıyken bu kayıt sitede hiç görünmez ama panelde durur — silmeden geçici olarak gizlemek için kullanışlıdır.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | SEO — <x-admin::form.seo> bileşenindeki alanlar
    |----------------------------------------------------------------------
    */
    'seo' => [

        'site_name' => [
            'title' => 'Site adı',
            'body' => <<<'HTML'
                <p>Arama motorlarına ve sosyal medyaya bildirilen sitenizin genel adı. Sayfa başlıklarının sonuna eklenebilir ve paylaşımlarda kaynak adı olarak görünür.</p>
                <p>Boş bırakırsanız <strong>Firma Bilgileri</strong>'ndeki firma adı kullanılır.</p>
                HTML,
        ],

        'focus_keyword' => [
            'title' => 'Odak anahtar kelime',
            'body' => <<<'HTML'
                <p>Bu sayfanın Google'da <strong>hangi aramada çıkmasını</strong> istediğinizi belirten ana ifade. Alttaki SEO analizi puanı bu kelimeye göre hesaplanır.</p>
                <p><strong>İyi bir odak kelime:</strong></p>
                <ul>
                    <li>Ziyaretçinin arama kutusuna gerçekten yazacağı ifadedir — örn. <code>gaziantep web tasarım</code></li>
                    <li>2–4 kelimedir; tek kelime çok rekabetli, çok uzun ifadeyi kimse aramaz</li>
                    <li>Her sayfada farklıdır — aynı kelimeyi iki sayfaya verirseniz sayfalar birbiriyle yarışır</li>
                </ul>
                <p>Seçtiğiniz kelimeyi başlıkta, ilk paragrafta, bir ara başlıkta ve mümkünse sayfa adresinde (slug) kullanın.</p>
                HTML,
        ],

        'meta_title' => [
            'title' => 'Meta başlık',
            'body' => <<<'HTML'
                <p>Google arama sonuçlarında görünen <strong>mavi, tıklanabilir başlık</strong>. Tarayıcı sekmesinde de bu yazı görünür.</p>
                <ul>
                    <li>İdeal uzunluk <strong>50–60 karakter</strong>; daha uzunu sonuç sayfasında “…” ile kesilir</li>
                    <li>En önemli kelimeyi başa koyun</li>
                    <li>Marka adını sona ekleyebilirsiniz: <code>Kurumsal Web Tasarım – Firma Adı</code></li>
                </ul>
                <p>Boş bırakırsanız sayfanın kendi başlığı kullanılır — çoğu sayfada bu yeterlidir.</p>
                HTML,
        ],

        'meta_description' => [
            'title' => 'Meta açıklama',
            'body' => <<<'HTML'
                <p>Arama sonuçlarında başlığın altındaki <strong>gri açıklama metni</strong>. Sıralamayı doğrudan etkilemez ama iyi yazılmış bir açıklama tıklanma oranını artırır.</p>
                <ul>
                    <li>İdeal uzunluk <strong>120–160 karakter</strong></li>
                    <li>Sayfanın ne sunduğunu net anlatın, bir eylem çağrısı ekleyin (“Hemen teklif alın”)</li>
                    <li>Odak anahtar kelimeyi doğal biçimde geçirin — Google, aranan kelimeyi koyu gösterir</li>
                </ul>
                <p>Boş bırakırsanız sayfanın özeti kullanılır.</p>
                HTML,
        ],

        'meta_keywords' => [
            'title' => 'Meta anahtar kelimeler',
            'body' => <<<'HTML'
                <p>Virgülle ayrılmış birkaç kelime. <strong>Google bu alanı yıllardır sıralamada kullanmıyor</strong> — kötüye kullanıldığı için devre dışı bıraktı.</p>
                <p>Yandex gibi bazı arama motorları hâlâ hafifçe dikkate alabilir. İsterseniz 3–5 gerçekten ilgili kelime yazın, isterseniz boş bırakın; Google performansına etkisi yoktur.</p>
                HTML,
        ],

        'og_media_id' => [
            'title' => 'Paylaşım görseli',
            'body' => <<<'HTML'
                <p>Sayfanın bağlantısı <strong>WhatsApp, Facebook, LinkedIn, X</strong> gibi yerlerde paylaşıldığında çıkan büyük önizleme görseli.</p>
                <ul>
                    <li>Önerilen boyut <strong>1200 × 630 piksel</strong></li>
                    <li>Üzerine küçük puntolu yazı koymayın — küçük önizlemede okunmaz</li>
                    <li>Boş bırakırsanız sayfanın kapak görseli kullanılır</li>
                </ul>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Schema.org — hem Ayarlar › Schema.org sekmesi hem kayıt bazlı bileşen
    |----------------------------------------------------------------------
    */
    'schema' => [

        'business_type' => [
            'title' => 'İşletme türü',
            'body' => <<<'HTML'
                <p>Sitenin sahibi kurumu Google'a hangi kategoride tanıtacağınızı seçer. Bu, arama sonuçlarında ve sağdaki bilgi panelinde işletmenizin nasıl görüneceğini etkiler.</p>
                <ul>
                    <li><strong>ProfessionalService</strong> — avukat, mimar, ajans, danışman, muhasebeci gibi hizmet veren işletmeler. Çoğu kurumsal site için doğru seçim.</li>
                    <li><strong>LocalBusiness</strong> — müşterilerin ziyaret ettiği fiziksel adresi olan işletmeler (mağaza, restoran, kuaför).</li>
                    <li><strong>Corporation</strong> — daha büyük, kurumsal yapıdaki şirketler (A.Ş. gibi).</li>
                    <li><strong>Organization</strong> — yukarıdakilere uymayan genel kurumlar. Bu seçilirse adres ve çalışma saatleri yayınlanmaz.</li>
                </ul>
                <p>Emin değilseniz <strong>ProfessionalService</strong> ile başlayın.</p>
                HTML,
        ],

        'founding_year' => [
            'title' => 'Kuruluş yılı',
            'body' => <<<'HTML'
                <p>Kurumun kurulduğu yıl — yalnızca yıl yazın (örn. <code>2015</code>).</p>
                <p>“Şu yıldan beri hizmet veriyoruz” bilgisini Google'ın anlayacağı biçimde işaretler; köklü olduğunuz izlenimini güçlendirir. Zorunlu değildir.</p>
                HTML,
        ],

        'price_range' => [
            'title' => 'Fiyat aralığı',
            'body' => <<<'HTML'
                <p>Hizmetlerinizin genel pahalılık seviyesini <strong>₺ işareti sayısıyla</strong> gösterir — kesin fiyat değil, kabaca bir gösterge.</p>
                <ul>
                    <li><code>₺</code> ekonomik &nbsp;·&nbsp; <code>₺₺</code> orta &nbsp;·&nbsp; <code>₺₺₺</code> üst &nbsp;·&nbsp; <code>₺₺₺₺</code> lüks</li>
                </ul>
                <p>Google yerel sonuçlarda gösterebilir. Belirtmek istemiyorsanız “Belirtilmesin” seçin.</p>
                HTML,
        ],

        'tax_id' => [
            'title' => 'Vergi / MERSİS numarası',
            'body' => <<<'HTML'
                <p>Kurumunuzun resmi kimlik numarası (vergi numarası ya da MERSİS no).</p>
                <p>Yapılandırılmış veride kurumun resmi kaydı olarak yer alır; sahte site ihtimaline karşı arama motorlarına güven verir. Zorunlu değildir, isteğe bağlıdır.</p>
                HTML,
        ],

        'tax_office' => [
            'title' => 'Vergi dairesi',
            'body' => <<<'HTML'
                <p>Bağlı olduğunuz vergi dairesinin adı (örn. <code>Şahinbey</code>).</p>
                <p>Kurum künyenizi tamamlar; genellikle vergi/MERSİS numarasıyla birlikte girilir. Zorunlu değildir.</p>
                HTML,
        ],

        'area_served' => [
            'title' => 'Hizmet verilen bölge',
            'body' => <<<'HTML'
                <p>Coğrafi olarak nerelere hizmet verdiğinizi Google'a bildirir. Yerel aramalarda (“yakınımdaki…”, “… şehrinde …”) etkilidir.</p>
                <ul>
                    <li>Tüm ülkeye hizmet veriyorsanız tek satır: <code>Türkiye</code></li>
                    <li>Belirli illere hizmet veriyorsanız <strong>her satıra bir il</strong> yazın</li>
                </ul>
                HTML,
        ],

        'description' => [
            'title' => 'Kısa tanım',
            'body' => <<<'HTML'
                <p>Kurumu bir-iki cümlede anlatan metin. Arama motorları kurumunuzu tanımak için kullanır.</p>
                <p>Reklam dili değil, <strong>ne yaptığınızı net</strong> anlatın: “X ilinde kurumsal web tasarımı ve dijital pazarlama hizmeti veren ajans.”</p>
                <p>Boş bırakırsanız Firma Bilgileri'ndeki kısa açıklama kullanılır.</p>
                HTML,
        ],

        'same_as' => [
            'title' => 'Ek profil adresleri',
            'body' => <<<'HTML'
                <p>Kurumunuzu temsil eden diğer sayfaların adresleri. Google bunları aynı kurumun farklı yerlerdeki profilleri olarak birbirine bağlar ve kimliğinizi daha iyi tanır.</p>
                <p><strong>Sosyal Medya</strong> ayarlarına eklediğiniz hesaplar (Facebook, Instagram, LinkedIn, X, YouTube) buraya <strong>otomatik</strong> eklenir — onları tekrar yazmayın.</p>
                <p>Buraya yalnızca şunları ekleyin, her satıra bir adres:</p>
                <ul>
                    <li>Wikipedia sayfanız</li>
                    <li>Google İşletme Profili adresiniz</li>
                    <li>Ticaret odası / meslek kuruluşu üye profiliniz</li>
                    <li>Sektörel rehberlerdeki firma sayfanız</li>
                </ul>
                HTML,
        ],

        'search_url' => [
            'title' => 'Site içi arama adresi',
            'body' => <<<'HTML'
                <p>Sitenizde ziyaretçilerin kelime yazıp arama yapabildiği bir <strong>arama kutusu</strong> varsa, bu alan Google'a aramanın hangi adreste yapıldığını söyler.</p>
                <p>Google bazen arama sonuçlarında, sitenizin adının altında küçük bir arama kutusu gösterir; onu buraya yazdığınız adres besler.</p>
                <p><strong>Nasıl bulunur:</strong> sitenizde bir şey aratın, tarayıcının adres çubuğundaki bağlantıya bakın. Aradığınız kelimenin geçtiği yeri <code>{query}</code> ile değiştirin.</p>
                <p><strong>Örnek:</strong> arama adresi <code>https://siteniz.com/ara?q=web+tasarim</code> ise, buraya <code>https://siteniz.com/ara?q={query}</code> yazın.</p>
                <p>Sitenizde arama özelliği yoksa bu alanı <strong>boş bırakın</strong>.</p>
                HTML,
        ],

        'opening_hours' => [
            'title' => 'Çalışma saatleri',
            'body' => <<<'HTML'
                <p>Her gün için açılış ve kapanış saatini girin; kapalı günlerde <strong>“Kapalı”</strong> kutusunu işaretleyin.</p>
                <p>Google, yerel sonuçlarda ve haritada işletmenizin <strong>“Açık / Kapanışa şu kadar var”</strong> bilgisini buradan gösterir.</p>
                <p>Yalnızca <strong>ProfessionalService</strong> ve <strong>LocalBusiness</strong> türlerinde yayınlanır. Öğle arası gibi bölünmüş saatler bu ekranda desteklenmez; kesintisiz aralık girin.</p>
                HTML,
        ],

        // Kayıt bazlı <x-admin::form.schema> bileşeni
        'record_type' => [
            'title' => 'Ana düğüm türünü değiştir',
            'body' => <<<'HTML'
                <p>Bu sayfa için Google'a otomatik bir tür bildiriliyor (blog yazısı, hizmet sayfası vb.). Bu alan <strong>yalnızca istisnai durumlarda</strong>, o türü elle değiştirmek içindir.</p>
                <p>Örneğin bir hizmet sayfasını adım adım tarif (<code>HowTo</code>) ya da etkinlik (<code>Event</code>) olarak işaretlemek isterseniz türü buraya yazarsınız.</p>
                <p><strong>Çoğu sayfada boş bırakın.</strong> Ne yazacağınızdan emin değilseniz dokunmayın — yanlış tür, arama görünümüne zarar verebilir.</p>
                HTML,
        ],

        'record_json' => [
            'title' => 'Ek JSON-LD (gelişmiş)',
            'body' => <<<'HTML'
                <p>İleri düzey kullanıcılar içindir. Bu sayfaya, otomatik üretilenlere <strong>ek olarak</strong> özel yapılandırılmış veri eklemenizi sağlar.</p>
                <p>Geçerli bir JSON metni yazın — tek bir <code>{ }</code> nesnesi ya da <code>[ ]</code> içinde birden çok nesne. Girdiğiniz içerik sayfanın işaretlemesine olduğu gibi eklenir.</p>
                <p>Nasıl kullanılacağını bilmiyorsanız boş bırakın; sayfanın SEO'su bundan etkilenmez.</p>
                HTML,
        ],

        'record_override' => [
            'title' => 'Otomatik üretimi kapat',
            'body' => <<<'HTML'
                <p>Normalde bu sayfa için tür bilgisi, menü yolu (breadcrumb) ve varsa SSS işaretlemesi <strong>otomatik</strong> üretilir.</p>
                <p>Bu anahtarı açarsanız otomatik üretim durur; yalnızca site geneli kurum bilgisi ve yukarıdaki <strong>Ek JSON-LD</strong> alanına yazdığınız metin yayınlanır.</p>
                <p>Yalnızca işaretlemeyi tümüyle elle yönetmek istiyorsanız açın. Aksi halde <strong>kapalı bırakın</strong>.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Analitik — Ayarlar › Analitik sekmesi
    |----------------------------------------------------------------------
    */
    'analytics' => [

        'property_id' => [
            'title' => 'GA4 Mülk Kimliği (Property ID)',
            'body' => <<<'HTML'
                <p>Google Analytics hesabınızda bu web sitesine karşılık gelen <strong>sayısal kimlik</strong> (örn. <code>493819123</code>).</p>
                <p><strong>Nereden bulunur:</strong></p>
                <ol>
                    <li><a href="https://analytics.google.com" target="_blank" rel="noopener">analytics.google.com</a> adresine girin</li>
                    <li>Sol altta <strong>Yönetici</strong> (dişli çark) → <strong>Mülk ayrıntıları</strong></li>
                    <li>Sağ üstteki <strong>MÜLK KİMLİĞİ</strong> altında yazan sayı</li>
                </ol>
                <p><strong>Dikkat:</strong> <code>G-XXXXXXXX</code> ile başlayan kod bu <em>değildir</em> — o “ölçüm kimliği”dir, buraya yazılmaz.</p>
                HTML,
        ],

        'service_account' => [
            'title' => 'Google kimlik dosyası (JSON)',
            'body' => <<<'HTML'
                <p>Panelin, Google Analytics verinizi sizin adınıza <strong>okuyabilmesi</strong> için Google'dan alınan bir yetki dosyası. Uzantısı <code>.json</code>'dur.</p>
                <p>Bu dosyayı nasıl oluşturacağınız <strong>alttaki adım adım kılavuzda</strong> anlatılıyor.</p>
                <ul>
                    <li>Sunucuda <strong>şifrelenerek</strong> saklanır, bir daha ekranda gösterilmez</li>
                    <li>İçinde şifreniz ya da kişisel veriniz yoktur; yalnızca bu siteye ait <strong>okuma</strong> yetkisi verir</li>
                    <li>Yetkiyi istediğiniz an Google tarafından iptal edebilirsiniz</li>
                </ul>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Site haritası — /admin/sitemap
    |----------------------------------------------------------------------
    */
    /*
    |----------------------------------------------------------------------
    | Search Console
    |----------------------------------------------------------------------
    */
    'search_console' => [

        'site_url' => [
            'title' => 'Search Console site adresi',
            'body' => <<<'HTML'
                <p>Google Search Console'da sitenizi eklediğinizde ona bir <strong>mülk</strong> adı verilir. Panelin hangi mülkün verisini okuyacağını buradan söylüyorsunuz. İki biçim olabilir:</p>
                <ul>
                    <li><code>sc-domain:siteniz.com</code> — “Alan adı” mülkü. <strong>www'lu, www'suz, http, https</strong> hepsini birlikte kapsar; tavsiye edilen budur.</li>
                    <li><code>https://siteniz.com/</code> — “URL öneki” mülkü. Yalnızca tam olarak bu adresi kapsar; sondaki eğik çizgiyi atlamayın.</li>
                </ul>
                <p>Elle yazmak yerine <strong>“Mülkleri listele”</strong> butonuna basın — Google hesabının eriştiği mülkleri getirir, tıklayıp seçersiniz. Liste boş geliyorsa, yukarıdaki 3. adımı (panelin e-posta adresini Search Console'a kullanıcı olarak ekleme) henüz tamamlamamışsınız demektir.</p>
                HTML,
        ],

        'queries' => [
            'title' => 'Hangi aramalarda çıkıyorsunuz',
            'body' => <<<'HTML'
                <p>İnsanların Google'a yazdığı kelimeler ve sitenizin o aramalardaki performansı:</p>
                <ul>
                    <li><strong>Gösterim</strong> — sonuç listesinde kaç kez göründünüz.</li>
                    <li><strong>Tıklama</strong> — kaç kişi gerçekten sitenize girdi.</li>
                    <li><strong>Oran</strong> — gösterimlerin yüzde kaçı tıklamaya dönüştü.</li>
                    <li><strong>Sıra</strong> — o aramada ortalama kaçıncı sıradaydınız (küçük olması iyidir; 1 en üst).</li>
                </ul>
                <p>Nasıl okunur: <strong>gösterimi yüksek ama tıklaması düşük</strong> bir arama, o sayfanın başlık ve açıklamasının yeterince çekici olmadığını gösterir — sayfanın SEO başlığını gözden geçirin. <strong>Sırası 8-20 arası</strong> olanlar ise en kârlı iş: küçük iyileştirmelerle ilk sayfaya çıkabilecek adaylardır.</p>
                <p>Veriler Google'dan gelir ve yaklaşık 2 gün gecikmelidir; dünün verisi henüz görünmeyebilir.</p>
                HTML,
        ],

        'sitemaps' => [
            'title' => 'Site haritaları',
            'body' => <<<'HTML'
                <p>Site haritanızı Google'a bir kez bildirmeniz yeterlidir; sonrasında Google onu düzenli olarak kendisi okur ve yeni sayfalarınızı daha çabuk keşfeder.</p>
                <p>Tablodaki sütunlar: <strong>Bildirilen adres</strong>, haritada kaç adres olduğunu; <strong>Son okuma</strong>, Google'ın dosyayı en son ne zaman indirdiğini gösterir. “Sorun yok” yazıyorsa yapacak bir şey kalmamıştır.</p>
                <p>Hata görürseniz genellikle sebebi, haritadaki bir adresin açılmaması ya da engellenmiş olmasıdır. İçeriklerinizi düzelttikten sonra Site Haritası ekranından yeniden oluşturup buradan tekrar gönderebilirsiniz.</p>
                HTML,
        ],

        'inspect' => [
            'title' => 'URL denetimi',
            'body' => <<<'HTML'
                <p>Tek bir sayfanın Google'daki durumunu sorar: <strong>indekslendi mi, ne zaman tarandı, bir engel var mı.</strong> Yeni yayınladığınız bir sayfanın Google'a girip girmediğini öğrenmenin en hızlı yolu budur.</p>
                <p>Sadece yol yazabilirsiniz (<code>/hakkimizda</code>); sistem tam adrese çevirir.</p>
                <p>Sık görülen yanıtlar:</p>
                <ul>
                    <li><strong>Gönderildi ve indekslendi</strong> — her şey yolunda, sayfa aramada çıkabilir.</li>
                    <li><strong>Keşfedildi, henüz indekslenmedi</strong> — Google sayfayı biliyor ama sıraya almış. Beklemek gerekir, acele etmeye gerek yok.</li>
                    <li><strong>Google bu adresi hiç görmemiş</strong> — site haritasını gönderdiğinizden emin olun; yeni sayfalar için birkaç gün normaldir.</li>
                    <li><strong>“İndekslenmesin” etiketiyle dışlandı</strong> — o sayfanın SEO bölümündeki arama motoru ayarı kapalı demektir; isteyerek yaptıysanız sorun yok.</li>
                </ul>
                <p>Google'ın günlük denetim hakkı sınırlıdır, bu yüzden aynı adresin sonucu 1 saat boyunca hafızada tutulur.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Gelen talepler
    |----------------------------------------------------------------------
    */
    'lead' => [

        'status' => [
            'title' => 'Durum',
            'body' => <<<'HTML'
                <p>Talebin hangi aşamada olduğunu gösterir. Ekipteki herkes aynı listeye baktığı için bu alan “bu mesajla kim ne yaptı” sorusunun cevabıdır.</p>
                <ul>
                    <li><strong>Yeni</strong> — henüz kimse ilgilenmedi.</li>
                    <li><strong>İşlemde</strong> — biri ilgileniyor, süreç devam ediyor. Panelden e-posta yanıtı gönderdiğinizde durum kendiliğinden buraya geçer.</li>
                    <li><strong>Tamamlandı</strong> — iş kapandı, yapılacak bir şey kalmadı.</li>
                    <li><strong>Spam</strong> — istenmeyen/otomatik mesaj. Silmek yerine bunu seçmek daha iyidir: kayıt durur, listeyi kirletmez.</li>
                </ul>
                HTML,
        ],

        'assigned_to' => [
            'title' => 'İlgilenen kişi',
            'body' => <<<'HTML'
                <p>Talebi kimin takip ettiğini belirtir. Birden fazla kişi panele giriyorsa aynı mesaja iki kişinin ayrı ayrı yanıt vermesini engeller.</p>
                <p>Listede panele girişi olan kullanıcılar görünür. Kimseyi seçmek zorunda değilsiniz; boş bıraktığınızda talep “atanmamış” olarak listelenir ve üstteki filtreden bunları tek tuşla görebilirsiniz.</p>
                HTML,
        ],

        'note' => [
            'title' => 'İç not',
            'body' => <<<'HTML'
                <p>Ekip içi not alanı. <strong>Müşteriye kesinlikle gönderilmez</strong>, yalnızca panelde görünür.</p>
                <p>Örnek kullanım: “Telefonla görüşüldü, fiyat teklifi hazırlanacak.” / “Bütçesi uygun değil, takip edilmeyecek.” Listede not içeren taleplerin yanında küçük bir not simgesi çıkar.</p>
                HTML,
        ],

        'reply' => [
            'title' => 'E-posta ile yanıtla',
            'body' => <<<'HTML'
                <p>Talebi gönderen kişiye panelden doğrudan e-posta yazmanızı sağlar — e-posta programınızı açmanız gerekmez. Yanıtın altına kişinin size gönderdiği mesaj da eklenir, böylece neyi yanıtladığınız karşı tarafta da belli olur.</p>
                <p>Yanıt, <strong>Ayarlar → Posta</strong> bölümünde tanımladığınız gönderici hesap üzerinden gider. Orada bir SMTP bilgisi girilmemişse e-posta gönderilemez ve uyarı alırsınız.</p>
                <p>Gönderim sonrası talep “yanıtlandı” olarak işaretlenir, durumu “Yeni” ise “İşlemde”ye geçer ve işlem log kayıtlarına yazılır. Yanıt metni panelde saklanmaz — kaydını tutmak isterseniz iç nota kısa bir özet yazın.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Hızlı İndeksleme (IndexNow)
    |----------------------------------------------------------------------
    */
    'indexnow' => [

        'enabled' => [
            'title' => 'IndexNow açık',
            'body' => <<<'HTML'
                <p>Açık olduğunda panel, değişen adresleri arama motorlarına anında bildirir. Normal şartlarda motorun sitenizi kendi kendine yeniden taraması günler sürebilir; bu bildirimle süre saatlere (bazen dakikalara) iner.</p>
                <p>Ücretsiz, kota yok ve SEO açısından bir riski yok — özel bir sebep yoksa açık bırakın.</p>
                <p><strong>Google bu protokolü desteklemez.</strong> Bildirimi Bing, Yandex ve diğer katılımcı motorlar alır. Google tarafı için Search Console ekranından site haritanızı bildirmeniz gerekir; ikisi birlikte çalışır.</p>
                HTML,
        ],

        'auto_submit' => [
            'title' => 'İçerik kaydedilince kendiliğinden bildir',
            'body' => <<<'HTML'
                <p>Açıkken bir sayfa, blog yazısı ya da hizmet kaydettiğinizde o adres kendiliğinden bildirilir — ayrıca bir şey yapmanız gerekmez.</p>
                <p><strong>Yayından çıkardığınız ya da sildiğiniz</strong> adresler de bildirilir. Bu kasıtlıdır: motor adresi yeniden tarar, sayfanın artık olmadığını görür ve arama sonuçlarından düşürür. Yoksa silinmiş bir sayfa haftalarca sonuçlarda kalabilir.</p>
                <p>Bildirim kuyrukta çalışır, yani kaydetme işlemi arama motorunun yanıtını beklemez. Kuyruk işçisi (<code>queue:work</code>) çalışmıyorsa bildirimler o açılana kadar bekler.</p>
                <p>Kapatırsanız bildirim tamamen durmaz — bu sayfadan elle gönderebilirsiniz.</p>
                HTML,
        ],

        'key' => [
            'title' => 'Doğrulama anahtarı',
            'body' => <<<'HTML'
                <p>Arama motorunun "bu bildirimi gerçekten site sahibi mi gönderdi?" sorusunu yanıtlayan rastgele bir koddur. Motor, sitenizin kökünde <code>anahtar.txt</code> adresini açar ve içinde aynı kodu görürse bildirimi kabul eder.</p>
                <p>Bu dosyayı sunucuya elle koymanız gerekmiyor — panel onu anahtardan üreterek kendisi sunuyor. "Anahtar dosyasını aç" bağlantısıyla kontrol edebilirsiniz; tarayıcıda sadece kodu görmeniz normaldir.</p>
                <p><strong>Anahtarı yenilemek</strong> yalnızca kodun başkasının eline geçtiğini düşünüyorsanız gerekir. Yenileyince eski kod geçersiz olur, yeni kod sonraki bildirimde kendiliğinden doğrulanır; başka bir işlem yapmanıza gerek kalmaz.</p>
                HTML,
        ],

        'manual' => [
            'title' => 'Elle bildirim',
            'body' => <<<'HTML'
                <p>Belirli adresleri hemen bildirmek için kullanılır. Her satıra bir adres yazın; yalnızca yol yazmanız yeterlidir (<code>/hakkimizda</code>), tam adrese kendiliğinden çevrilir.</p>
                <p>Ne zaman işe yarar: kuyruk işçisi kapalıyken yapılan bir değişiklikten sonra, ya da panel dışında yaptığınız bir güncellemeden sonra.</p>
                <p><strong>"Tüm adresleri bildir"</strong> butonu site haritasındaki bütün adresleri gönderir. Bunu yalnızca siteyi ilk kez yayına aldığınızda ya da büyük bir adres değişikliği sonrasında kullanın — her gün tekrarlamanın faydası yoktur, aşırı kullanımda motorlar bildirimleri geçici olarak sınırlayabilir.</p>
                <p>Not: bildirdiğiniz adres, sitenin kendi alan adında olmak zorundadır; başka alan adına ait satırlar sessizce atlanır (protokol bunu şart koşuyor).</p>
                HTML,
        ],
    ],

    'sitemap' => [

        'sources' => [
            'title' => 'Kaynaklar',
            'body' => <<<'HTML'
                <p>Site haritasına (sitemap.xml) hangi adres gruplarının gireceğini seçer. Kapatılan bir kaynağın adresleri sitemap'ten hemen çıkarılır — sayfalar yine yayında kalır, sadece Google'a bu listeden bildirilmez.</p>
                <p>Kapalı bırakmak için bir sebep yoksa hepsini açık tutun; Google daha çok sayfanızı hızlıca bulur.</p>
                HTML,
        ],

        'excluded_urls' => [
            'title' => 'Hariç tutulan adresler',
            'body' => <<<'HTML'
                <p>Yayında olsa da site haritasına <strong>girmesini istemediğiniz</strong> adresler. Her satıra bir adres yazın — tam adres (<code>https://siteniz.com/kampanya</code>) ya da yalnızca yol (<code>/kampanya</code>) kabul edilir.</p>
                <p>Örnek kullanım: kısa süreliğine yayınladığınız bir kampanya sayfası, test amaçlı bir sayfa, ya da başka bir yolla (ör. doğrudan bağlantıyla) paylaştığınız ama aramada çıkmasını istemediğiniz bir sayfa.</p>
                HTML,
        ],

        'extra_urls' => [
            'title' => 'Ek adresler',
            'body' => <<<'HTML'
                <p>Sistemin kendiliğinden bulamadığı ama site haritasına eklemek istediğiniz adresler. Her satıra bir <strong>tam adres</strong> yazın (<code>https://siteniz.com/...</code>).</p>
                <p>Örnek: panelde yönetilmeyen özel bir açılış sayfası, dışarıdan bağlanan bir PDF/katalog adresi.</p>
                HTML,
        ],

        'robots_txt' => [
            'title' => 'robots.txt',
            'body' => <<<'HTML'
                <p><strong>robots.txt</strong>, arama motoru robotlarının sitenize girdiğinde ilk okuduğu küçük metin dosyasıdır. “Şuraya girebilirsin, şuraya girme” demenin standart yoludur.</p>
                <p>Satırların anlamı:</p>
                <ul>
                    <li><code>User-agent: *</code> — aşağıdaki kurallar <strong>tüm</strong> robotlar için geçerli.</li>
                    <li><code>Disallow: /admin</code> — bu adres ve altındakiler taranmasın (yönetim paneli aramada çıkmasın).</li>
                    <li><code>Allow: /ornek</code> — kapatılmış bir bölümün içinde tek bir adrese izin verir.</li>
                </ul>
                <p>Site haritası satırını <strong>siz yazmayın</strong> — sistem, sitenin o anki adresini kullanarak çıktının sonuna kendiliğinden ekliyor.</p>
                <p><strong>Dikkat:</strong> burada bir adresi kapatmak onu gizlemez, sadece taranmasını engeller. Bir sayfanın aramada <em>çıkmamasını</em> istiyorsanız doğru yer bu değil; o sayfanın SEO bölümündeki arama motoru ayarıdır.</p>
                <p>Ne yaptığınızdan emin değilseniz varsayılan içeriği olduğu gibi bırakın — çoğu site için yeterlidir. Yanlış bir <code>Disallow: /</code> satırı sitenizin tamamını aramadan düşürebilir.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Blog yazısı formu
    |----------------------------------------------------------------------
    */
    'blog' => [
        'title' => [
            'title' => 'Başlık',
            'body' => <<<'HTML'
                <p>Yazının adı. Hem sayfanın en üstünde büyük puntoyla, hem liste kartlarında, hem de (meta başlık boşsa) Google sonuçlarında görünür.</p>
                <p>Merak uyandıran ama içeriği doğru yansıtan bir başlık yazın; okuyucunun aradığı ifadeyi içersin.</p>
                HTML,
        ],
        'is_featured' => [
            'title' => 'Öne çıkar',
            'body' => <<<'HTML'
                <p>Açıkken bu yazı, blog listesinin üstündeki “öne çıkanlar” bölümünde ya da ana sayfada vurgulu gösterilir (temaya göre değişir).</p>
                <p>Aynı anda birkaç yazıyı öne çıkarabilirsiniz; en güncel/önemli 2–3 tanesini seçmek en iyisidir.</p>
                HTML,
        ],
        'blog_category_id' => [
            'title' => 'Kategori',
            'body' => <<<'HTML'
                <p>Yazının ait olduğu ana konu grubu. Her yazı en fazla bir kategoriye girer (daha ince ayrım için etiketleri kullanın).</p>
                <p>Kategori adreste de görünebilir ve blogunuzun menü yapısını oluşturur. Kategorisiz de bırakabilirsiniz.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Hizmet formu
    |----------------------------------------------------------------------
    */
    'service' => [
        'title' => [
            'title' => 'Başlık',
            'body' => <<<'HTML'
                <p>Hizmetin adı. İçinde <code>@{{region}}</code>, <code>@{{city}}</code>, <code>@{{district}}</code> yazabilirsiniz — her bölge sayfasında o bölgenin adıyla değişir.</p>
                <p>Örn. “<code>@{{region}}</code> Web Tasarım” başlığı, Gaziantep › Şahinbey sayfasında “Gaziantep Şahinbey Web Tasarım” olur.</p>
                HTML,
        ],
        'excerpt' => [
            'title' => 'Açıklama',
            'body' => <<<'HTML'
                <p>Hizmeti özetleyen kısa metin. Hizmet listelerinde ve arama sonuçlarında görünür. Yer tutucular (<code>@{{region}}</code> vb.) burada da çalışır.</p>
                HTML,
        ],
        'content' => [
            'title' => 'İçerik',
            'body' => <<<'HTML'
                <p>Hizmetin detaylı anlatımı. Metinde <code>@{{region}}</code>, <code>@{{city}}</code>, <code>@{{district}}</code> yer tutucuları her bölge sayfasında otomatik değişir — böylece aynı hizmetin farklı bölge sayfaları birbirinin kopyası olmaz.</p>
                <p>Bölgeye özel ek metin, o bölge kaydının “Bölgeye Özel Metin” alanından eklenir.</p>
                HTML,
        ],
        'service_regions' => [
            'title' => 'Hizmet bölgeleri',
            'body' => <<<'HTML'
                <p>Bu hizmetin sunulacağı iller/ilçeler. Seçtiğiniz her bölge için ayrı bir sayfa oluşur (örn. <code>/gaziantep/web-tasarim</code>), yer tutucular o bölgenin adıyla dolar.</p>
                <p>“Tüm illeri seç” ile hızlıca hepsini ekleyebilirsiniz. Çok sayıda bölge = çok sayıda sayfa; her birinin özgün içeriği olmasına dikkat edin.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Sayfa formu
    |----------------------------------------------------------------------
    */
    'page' => [
        'title' => [
            'title' => 'Başlık',
            'body' => <<<'HTML'
                <p>Sayfanın adı. Sayfanın üstünde, tarayıcı sekmesinde, menüye eklenirse menüde ve (meta başlık boşsa) arama sonuçlarında görünür.</p>
                HTML,
        ],
        'parent_id' => [
            'title' => 'Üst sayfa',
            'body' => <<<'HTML'
                <p>Bu sayfayı başka bir sayfanın altına yerleştirir. Adres de buna göre oluşur:</p>
                <ul>
                    <li>Üst seçilmezse: <code>siteniz.com/kariyer</code></li>
                    <li>Üst = “Kurumsal” ise: <code>siteniz.com/kurumsal/kariyer</code></li>
                </ul>
                <p>İç içe sayfalar, ana menüde açılır alt menü yapısı kurmak için kullanışlıdır. Bir sayfa kendi alt sayfasının altına konamaz.</p>
                HTML,
        ],
        'template' => [
            'title' => 'Şablon',
            'body' => <<<'HTML'
                <p>Sayfanın hangi düzende gösterileceğini seçer. Seçtiğinizde altında o şablonun ne yaptığının açıklaması çıkar.</p>
                <ul>
                    <li><strong>Varsayılan</strong> — standart içerik sayfası; çoğu sayfa için budur</li>
                    <li>Diğer şablonlar — özel düzenler (tam genişlik, iletişim, açılış sayfası vb.)</li>
                </ul>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Yönlendirme (redirect) modalı
    |----------------------------------------------------------------------
    */
    'redirect' => [
        'from_path' => [
            'title' => 'Kaynak adres',
            'body' => <<<'HTML'
                <p>Ziyaretçinin/Google'ın girmeye çalıştığı <strong>eski</strong> adres — sitenizin kök dizininden itibaren yazın.</p>
                <p>Örn. <code>/eski-hizmetler</code> ya da <code>/blog/2019/kampanya</code>. Alan adını (<code>https://siteniz.com</code>) yazmayın.</p>
                <p>Genellikle bir sayfayı sildiğinizde ya da adresini değiştirdiğinizde, eski adrese gelenleri kaybetmemek için oluşturulur.</p>
                HTML,
        ],
        'match_type' => [
            'title' => 'Eşleşme tipi',
            'body' => <<<'HTML'
                <p><strong>Tam eşleşme:</strong> yalnızca kaynak adresin birebir aynısı yönlendirilir. En güvenli seçenek.</p>
                <p><strong>Ön ek (başlangıç):</strong> kaynak adresle <em>başlayan</em> tüm adresler yönlendirilir. Örn. <code>/blog/eski</code> → altındaki tüm yazılar. Dikkatli kullanın, istemeden çok fazla adresi kapsayabilir.</p>
                HTML,
        ],
        'to_url' => [
            'title' => 'Hedef',
            'body' => <<<'HTML'
                <p>Ziyaretçinin <strong>yönlendirileceği yeni</strong> adres.</p>
                <ul>
                    <li>Kendi sitenizde bir sayfa: <code>/yeni-hizmetler</code></li>
                    <li>Başka bir site: tam adres — <code>https://baskasite.com/sayfa</code></li>
                    <li>Karşılığı yoksa ana sayfa: <code>/</code></li>
                </ul>
                HTML,
        ],
        'status_code' => [
            'title' => 'Durum kodu',
            'body' => <<<'HTML'
                <p><strong>301 – Kalıcı:</strong> “bu sayfa temelli taşındı” demektir. Google eski adresin sıralama gücünü yeni adrese aktarır. Normal durumda bunu seçin.</p>
                <p><strong>302 – Geçici:</strong> “şimdilik başka yere bakın, eski adres geri gelecek”. Kampanya, bakım gibi geçici durumlar için. Google eski adresi indekste tutar.</p>
                HTML,
        ],
        'notes' => [
            'title' => 'Not',
            'body' => <<<'HTML'
                <p>Yalnızca sizin ve ekibinizin göreceği bir açıklama: bu yönlendirme neden var, ne zamana kadar geçerli. Aylar sonra listeye bakınca hatırlamak için çok işe yarar.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Rol formu
    |----------------------------------------------------------------------
    */
    'role' => [
        'name' => [
            'title' => 'Rol adı',
            'body' => <<<'HTML'
                <p>Sistemin içinde kullanılan kimlik. <strong>Küçük harf, İngilizce, boşluksuz</strong> yazın — örn. <code>editor</code>, <code>content-manager</code>.</p>
                <p>Kaydettikten sonra değiştirmeyin; yetki kontrolleri bu ada göre yapılır.</p>
                HTML,
        ],
        'label' => [
            'title' => 'Görünen ad',
            'body' => <<<'HTML'
                <p>Panelde ve kullanıcı listesinde gösterilen okunabilir ad — örn. <code>İçerik Editörü</code>. İstediğiniz zaman değiştirebilirsiniz.</p>
                HTML,
        ],
        'guard_name' => [
            'title' => 'Guard',
            'body' => <<<'HTML'
                <p>Yetki sisteminin çalıştığı alan. Panel kullanıcıları için her zaman <code>web</code> olmalıdır — <strong>değiştirmeyin</strong>.</p>
                HTML,
        ],
        'permissions' => [
            'title' => 'Yetkiler',
            'body' => <<<'HTML'
                <p>Bu role verilen izinler. Bir kullanıcıya birden çok rol atanabilir; izinler toplanır (biri izin veriyorsa yeterli).</p>
                <p>Grup başlığındaki <strong>“Tümünü Seç”</strong> ile bir modülün tüm izinlerini bir kerede açarsınız.</p>
                <p><code>super-admin</code> rolü listeden bağımsız olarak <strong>her şeyi</strong> yapabilir.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Firma bilgileri — Ayarlar › Firma
    |----------------------------------------------------------------------
    */
    'company' => [
        'logo_media_id' => [
            'title' => 'Firma logosu',
            'body' => <<<'HTML'
                <p>Üst menüde, site altbilgisinde ve Google arama önizlemesinde kullanılır. Ayrıca yapılandırılmış veride kurumunuzun logosu olarak bildirilir.</p>
                <p>Şeffaf zeminli <strong>PNG</strong> ya da <strong>SVG</strong> önerilir. Koyu tema için de okunur bir logo seçin.</p>
                HTML,
        ],
        'name' => [
            'title' => 'Firma adı',
            'body' => <<<'HTML'
                <p>Markanızın günlük kullanılan adı (örn. <code>Umay Dijital</code>). Menü, altbilgi, e-postalar ve arama motoru işaretlemesi bu adı kullanır.</p>
                HTML,
        ],
        'legal_name' => [
            'title' => 'Yasal unvan',
            'body' => <<<'HTML'
                <p>Ticaret sicilindeki tam resmi unvan (örn. <code>Umay Dijital Yazılım A.Ş.</code>). Sözleşme/fatura künyesi ve yapılandırılmış veride resmi ad olarak kullanılır. Marka adıyla aynıysa boş bırakabilirsiniz.</p>
                HTML,
        ],
        'phone' => [
            'title' => 'Telefon',
            'body' => <<<'HTML'
                <p>Ana iletişim numarası. Altbilgide, iletişim sayfasında ve mobil cihazlarda “ara” bağlantısı olarak görünür.</p>
                <p>Ülke koduyla yazmak (örn. <code>+90 212 000 00 00</code>) uluslararası uyumluluk açısından iyidir.</p>
                HTML,
        ],
        'fax' => [
            'title' => 'Faks',
            'body' => <<<'HTML'
                <p>Varsa faks numarası. Çoğu işletme için gereksizdir — boş bırakabilirsiniz.</p>
                HTML,
        ],
        'email' => [
            'title' => 'E-posta',
            'body' => <<<'HTML'
                <p>Genel iletişim adresi (örn. <code>info@siteniz.com</code>). Altbilgide ve iletişim sayfasında görünür; iletişim formunda alıcı adresi boş bırakılırsa mesajlar buraya gelir.</p>
                HTML,
        ],
        'address' => [
            'title' => 'Adres',
            'body' => <<<'HTML'
                <p>Açık posta adresi. Altbilgide, iletişim sayfasında ve yerel SEO için yapılandırılmış veride kullanılır.</p>
                <p>Google İşletme Profili'nizdeki adresle <strong>birebir aynı</strong> yazın — tutarlılık yerel sıralamaya yardım eder.</p>
                HTML,
        ],
        'short_description' => [
            'title' => 'Kısa açıklama',
            'body' => <<<'HTML'
                <p>Site altbilgisinde, logonun altında görünen 1–2 cümlelik tanıtım. Ne yaptığınızı ve kime hizmet verdiğinizi sade bir dille anlatın.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | İletişim ayarları — Ayarlar › İletişim
    |----------------------------------------------------------------------
    */
    'contact' => [
        'enabled' => [
            'title' => 'İletişim formunu göster',
            'body' => <<<'HTML'
                <p>Kapalıyken iletişim sayfasındaki form gizlenir; telefon, e-posta ve harita görünmeye devam eder. Formu geçici olarak (spam yağmuru vb.) kapatmak için kullanışlıdır.</p>
                HTML,
        ],
        'to_email' => [
            'title' => 'Alıcı e-posta',
            'body' => <<<'HTML'
                <p>Form doldurulunca mesajın gönderileceği adres. <strong>Boş bırakırsanız</strong> Firma Bilgileri'ndeki e-posta kullanılır.</p>
                <p>Birden çok kişiye göndermek isterseniz bir dağıtım/grup adresi kullanın.</p>
                HTML,
        ],
        'cc_email' => [
            'title' => 'Bilgi kopyası (CC)',
            'body' => <<<'HTML'
                <p>Her form mesajının bir kopyasının gideceği ikinci adres. Örn. yönetici de görsün istiyorsanız. İsteğe bağlı.</p>
                HTML,
        ],
        'subject' => [
            'title' => 'E-posta konusu',
            'body' => <<<'HTML'
                <p>Size gelen bildirim e-postasının konu satırı. Şu değişkenleri kullanabilirsiniz: <code>{name}</code>, <code>{email}</code>, <code>{phone}</code>.</p>
                <p>Örn. <code>Web sitesi iletişim: {name}</code> → gelen kutusunda ayırt etmesi kolay olur.</p>
                HTML,
        ],
        'heading' => [
            'title' => 'Form başlığı',
            'body' => <<<'HTML'
                <p>İletişim formunun üstünde görünen başlık — örn. “Bize Yazın” ya da “Ücretsiz Teklif Alın”.</p>
                HTML,
        ],
        'intro' => [
            'title' => 'Form açıklaması',
            'body' => <<<'HTML'
                <p>Başlığın altındaki kısa metin. Ne kadar sürede dönüş yapacağınızı ya da hangi konularda yazılabileceğini belirtebilirsiniz.</p>
                HTML,
        ],
        'success_message' => [
            'title' => 'Başarı mesajı',
            'body' => <<<'HTML'
                <p>Form başarıyla gönderildikten sonra ziyaretçiye gösterilen metin — örn. “Mesajınız alındı, en kısa sürede döneceğiz.”</p>
                HTML,
        ],
        'error_message' => [
            'title' => 'Hata mesajı',
            'body' => <<<'HTML'
                <p>Teknik bir sorun yüzünden form gönderilemezse gösterilen metin. Alternatif bir iletişim yolu (telefon) vermek iyi olur.</p>
                HTML,
        ],
        'auto_reply_enabled' => [
            'title' => 'Otomatik yanıt gönder',
            'body' => <<<'HTML'
                <p>Açıkken, formu dolduran ziyaretçiye anında bir <strong>“mesajınızı aldık”</strong> e-postası gider. Profesyonel bir izlenim bırakır ve kişiye e-postanızın ulaştığını gösterir.</p>
                HTML,
        ],
        'auto_reply_subject' => [
            'title' => 'Yanıt konusu',
            'body' => <<<'HTML'
                <p>Ziyaretçiye giden otomatik teşekkür e-postasının konusu — örn. “Mesajınız bize ulaştı”.</p>
                HTML,
        ],
        'auto_reply_body' => [
            'title' => 'Yanıt metni',
            'body' => <<<'HTML'
                <p>Ziyaretçiye giden otomatik e-postanın gövdesi. Şu değişkenler kullanılabilir: <code>{name}</code>, <code>{email}</code>, <code>{phone}</code>, <code>{message}</code>.</p>
                <p>Kişinin gönderdiği mesajı <code>{message}</code> ile geri özetlemek güven verir.</p>
                HTML,
        ],
        'privacy_required' => [
            'title' => 'Formda onay kutusu iste',
            'body' => <<<'HTML'
                <p>Açıkken ziyaretçi, formu göndermeden önce KVKK aydınlatma metnini okuduğuna dair bir kutuyu işaretlemek zorunda kalır. <strong>KVKK uyumu için önerilir.</strong></p>
                HTML,
        ],
        'privacy_text' => [
            'title' => 'Onay metni',
            'body' => <<<'HTML'
                <p>Onay kutusunun yanında yazan metin. İçinde <code>{kvkk}</code> yazarsanız oraya aydınlatma metni sayfasının bağlantısı konur.</p>
                <p>Örn. <code>{kvkk} metnini okudum ve kişisel verilerimin işlenmesini kabul ediyorum.</code> Metnin kendisi İçerikler sekmesinden yönetilir.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | E-posta (SMTP) ayarları — Ayarlar › E-posta
    |----------------------------------------------------------------------
    */
    'mail' => [
        'host' => [
            'title' => 'SMTP sunucusu',
            'body' => <<<'HTML'
                <p>E-posta gönderimini yapan sunucunun adresi. E-posta sağlayıcınız verir — örn. <code>smtp.gmail.com</code>, <code>smtp.yandex.com</code> ya da hosting firmanızın <code>mail.siteniz.com</code> adresi.</p>
                <p>Bu bilgiler olmadan iletişim formu mesajları ve bildirim e-postaları gönderilemez.</p>
                HTML,
        ],
        'username' => [
            'title' => 'Kullanıcı adı',
            'body' => <<<'HTML'
                <p>SMTP sunucusuna giriş için kullanılan ad — genellikle e-posta adresinizin tamamı (örn. <code>info@siteniz.com</code>).</p>
                HTML,
        ],
        'password' => [
            'title' => 'Şifre',
            'body' => <<<'HTML'
                <p>SMTP hesabının şifresi. Sunucuda <strong>şifrelenerek</strong> saklanır ve panele bir daha gösterilmez.</p>
                <p>Gmail/Google Workspace kullanıyorsanız normal şifre değil, <strong>“uygulama şifresi”</strong> oluşturmanız gerekir.</p>
                <p>Kayıtlıysa alan boş görünür; dokunmazsanız eski şifre korunur.</p>
                HTML,
        ],
        'encryption' => [
            'title' => 'Şifreleme',
            'body' => <<<'HTML'
                <p>E-postanın sunucuya giderken nasıl şifreleneceği. Sağlayıcınızın belgesine bakın; genel kural:</p>
                <ul>
                    <li><strong>TLS</strong> → çoğunlukla port <code>587</code></li>
                    <li><strong>SSL</strong> → çoğunlukla port <code>465</code></li>
                    <li><strong>Yok</strong> → yalnızca test/yerel sunucu; canlıda kullanmayın</li>
                </ul>
                HTML,
        ],
        'port' => [
            'title' => 'Port',
            'body' => <<<'HTML'
                <p>Sunucuya bağlanılan kapı numarası. Şifreleme seçimiyle uyumlu olmalı: TLS için genelde <code>587</code>, SSL için <code>465</code>.</p>
                HTML,
        ],
        'from_name' => [
            'title' => 'Gönderen adı',
            'body' => <<<'HTML'
                <p>Gönderdiğiniz e-postalarda alıcının gelen kutusunda görünen isim — örn. <code>Umay Dijital</code>. Boşsa firma adı kullanılır.</p>
                HTML,
        ],
        'from_address' => [
            'title' => 'Gönderen e-posta',
            'body' => <<<'HTML'
                <p>E-postaların “kimden” adresi. Spam'e düşmemek için <strong>kendi alan adınızdaki</strong> bir adres olmalı (örn. <code>info@siteniz.com</code>) — <code>@gmail.com</code> gibi değil.</p>
                <p>Genellikle kullanıcı adıyla aynıdır.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Çerez çubuğu — Ayarlar › Çerez
    |----------------------------------------------------------------------
    */
    'cookie' => [
        'enabled' => [
            'title' => 'Çerez çubuğunu göster',
            'body' => <<<'HTML'
                <p>Açıkken ziyaretçiye çerez onayı çubuğu gösterilir ve <strong>onay verilene kadar</strong> analitik/pazarlama kodları yüklenmez. KVKK/GDPR uyumu için gereklidir.</p>
                <p>Kapalıyken tüm izleme kodları herkese basılır — yalnızca hiç izleme kullanmıyorsanız ya da hukuki sorumluluğu kabul ediyorsanız.</p>
                HTML,
        ],
        'title' => [
            'title' => 'Başlık',
            'body' => '<p>Çerez çubuğunun üstündeki kısa başlık — örn. “Çerez Tercihleri”.</p>',
        ],
        'description' => [
            'title' => 'Açıklama',
            'body' => <<<'HTML'
                <p>Çerezleri neden kullandığınızı anlatan metin. İçinde <code>{policy}</code> yazarsanız oraya çerez politikası sayfasının bağlantısı konur.</p>
                HTML,
        ],
        'policy_label' => [
            'title' => 'Politika bağlantı metni',
            'body' => '<p>Açıklama metnindeki <code>{policy}</code> yerine geçen tıklanabilir yazı — örn. “çerez politikası”.</p>',
        ],
        'accept_label' => [
            'title' => 'Kabul düğmesi',
            'body' => '<p>Tüm çerezleri kabul et düğmesinin yazısı — örn. “Tümünü Kabul Et”. Kısa tutun.</p>',
        ],
        'reject_label' => [
            'title' => 'Reddet düğmesi',
            'body' => <<<'HTML'
                <p>Zorunlu olmayan tüm çerezleri reddet düğmesinin yazısı — örn. “Reddet”.</p>
                <p>Bu düğme kabul düğmesiyle <strong>eşit görünürlükte</strong> gösterilir; KVKK/GDPR bunu ister.</p>
                HTML,
        ],
        'customize_label' => [
            'title' => 'Tercihler düğmesi',
            'body' => '<p>Ziyaretçinin çerez kategorilerini tek tek seçebileceği paneli açan düğmenin yazısı — örn. “Tercihleri Yönet”.</p>',
        ],
        'save_label' => [
            'title' => 'Kaydet düğmesi',
            'body' => '<p>Tercih panelinde seçimleri kaydeden düğmenin yazısı — örn. “Seçimimi Kaydet”.</p>',
        ],
        'necessary_title' => [
            'title' => 'Zorunlu çerezler — başlık',
            'body' => '<p>Sitenin çalışması için şart olan çerezlerin kategori adı. Bu kategori her zaman açıktır, ziyaretçi kapatamaz.</p>',
        ],
        'necessary_description' => [
            'title' => 'Zorunlu çerezler — açıklama',
            'body' => '<p>Oturum, güvenlik, dil tercihi gibi temel çerezleri sade bir dille anlatın.</p>',
        ],
        'functional_title' => [
            'title' => 'İşlevsel çerezler — başlık',
            'body' => '<p>Siteyi daha kullanışlı yapan ama şart olmayan çerezlerin kategori adı (örn. canlı destek widget'."'".'ı).</p>',
        ],
        'functional_description' => [
            'title' => 'İşlevsel çerezler — açıklama',
            'body' => '<p>Canlı destek, video gömme, harita gibi özelliklerin çerezlerini anlatın.</p>',
        ],
        'analytics_title' => [
            'title' => 'Analitik çerezler — başlık',
            'body' => '<p>Ziyaretçi davranışını ölçen çerezlerin kategori adı (Google Analytics vb.).</p>',
        ],
        'analytics_description' => [
            'title' => 'Analitik çerezler — açıklama',
            'body' => '<p>Hangi sayfaların ziyaret edildiğini anlamak için istatistik topladığınızı, verinin toplu ve anonim olduğunu belirtin.</p>',
        ],
        'marketing_title' => [
            'title' => 'Pazarlama çerezler — başlık',
            'body' => '<p>Reklam ve yeniden hedefleme çerezlerinin kategori adı (Meta Pixel, Google Ads vb.).</p>',
        ],
        'marketing_description' => [
            'title' => 'Pazarlama çerezler — açıklama',
            'body' => '<p>İlgi alanına göre reklam göstermek ve reklam performansını ölçmek için kullanıldığını belirtin.</p>',
        ],
        'lifetime_days' => [
            'title' => 'Geçerlilik (gün)',
            'body' => <<<'HTML'
                <p>Ziyaretçinin verdiği çerez onayının kaç gün hatırlanacağı. Süre dolunca yeniden sorulur. Yaygın değer <strong>180</strong> gündür (6 ay).</p>
                HTML,
        ],
        'version' => [
            'title' => 'Politika sürümü',
            'body' => <<<'HTML'
                <p>Çerez politikanızı önemli ölçüde değiştirdiğinizde bu sayıyı <strong>1 artırın</strong>. Böylece daha önce onay vermiş herkesten yeni politikaya göre tekrar onay istenir.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Bakım modu — Ayarlar › Bakım
    |----------------------------------------------------------------------
    */
    'maintenance' => [
        'enabled' => [
            'title' => 'Bakım modunu aç',
            'body' => <<<'HTML'
                <p>Açıkken ziyaretçilere sitenin ön yüzü yerine bir <strong>“bakımdayız”</strong> sayfası gösterilir. Yönetim paneli ve giriş yapmış kullanıcılar siteyi normal görmeye devam eder.</p>
                <p>Bu, sunucuyu tamamen kapatan komut değildir; panel çalışmaya devam eder.</p>
                HTML,
        ],
        'title' => [
            'title' => 'Başlık',
            'body' => '<p>Bakım sayfasındaki büyük başlık — örn. “Kısa Bir Aradayız”.</p>',
        ],
        'message' => [
            'title' => 'Mesaj',
            'body' => <<<'HTML'
                <p>Bakım sayfasındaki açıklama. Ne zaman döneceğinizi ve acil durumda nasıl ulaşılacağını yazın.</p>
                HTML,
        ],
        'retry_after' => [
            'title' => 'Yeniden deneme (dakika)',
            'body' => <<<'HTML'
                <p>Arama motorlarına “<strong>şu kadar dakika sonra tekrar gel</strong>” bilgisi gönderilir; böylece Google bakım sayfasını kalıcı sanıp sıralamanıza zarar vermez.</p>
                <p>Tahmini bakım süresini yazın. Boş bırakılabilir.</p>
                HTML,
        ],
        'bypass_secret' => [
            'title' => 'Önizleme anahtarı',
            'body' => <<<'HTML'
                <p>Bu anahtarı içeren özel bir bağlantıyla, <strong>giriş yapmamış</strong> biri (müşteri, iş ortağı) bakım sırasında siteyi görebilir.</p>
                <p>Harf, rakam, tire ve alt çizgi kullanın — örn. <code>musteri-onizleme</code>. Kaydedince tam bağlantı aşağıda çıkar. Boşsa bu yol kapalıdır.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | İzleme kodları — Ayarlar › İzleme Kodları
    |----------------------------------------------------------------------
    */
    'tracking' => [
        'ga4_id' => [
            'title' => 'Google Analytics 4',
            'body' => <<<'HTML'
                <p>Ziyaretçi istatistiklerini toplayan Google Analytics 4'ün <strong>ölçüm kimliği</strong>. <code>G-</code> ile başlar (örn. <code>G-ABCD1234EF</code>).</p>
                <p><strong>Nereden:</strong> analytics.google.com → Yönetici → <em>Veri Akışları</em> → web akışınız → sağ üstte “Ölçüm Kimliği”.</p>
                <p>Aşağıya Google Tag Manager kimliği girerseniz bu alan devre dışı kalır — Analytics'i Tag Manager içinden eklemelisiniz, yoksa ziyaretler iki kez sayılır.</p>
                HTML,
        ],
        'gtm_id' => [
            'title' => 'Google Tag Manager',
            'body' => <<<'HTML'
                <p>Tüm izleme ve reklam kodlarını <strong>tek yerden</strong> (Google arayüzünden, koda dokunmadan) yönetmenizi sağlayan kapsayıcı. Kimliği <code>GTM-</code> ile başlar.</p>
                <p><strong>Nereden:</strong> tagmanager.google.com → kabınızın kimliği üstte yazar.</p>
                <p>İleri düzey bir araçtır; kurulumunu bilmiyorsanız boş bırakıp yukarıya doğrudan GA4 kimliğini girmeniz yeterli.</p>
                HTML,
        ],
        'google_site_verification' => [
            'title' => 'Search Console doğrulama',
            'body' => <<<'HTML'
                <p>Google Search Console'da siteye sahip olduğunuzu kanıtlamak için “HTML etiketi” yönteminde verilen koddur.</p>
                <p>Google size <code>&lt;meta name="google-site-verification" content="<strong>uzun-kod</strong>"&gt;</code> verir — buraya yalnızca <strong>tırnak içindeki uzun kodu</strong> yapıştırın.</p>
                <p>Sıralamayı etkilemez; Search Console raporlarını (aramalar, hatalar) açar.</p>
                HTML,
        ],
        'bing_uet_id' => [
            'title' => 'Bing UET',
            'body' => <<<'HTML'
                <p>Microsoft (Bing) reklamları verirken dönüşümleri ölçen “UET etiketi” kimliği — yalnızca sayı. Bing'de reklam vermiyorsanız boş bırakın.</p>
                HTML,
        ],
        'bing_verification' => [
            'title' => 'Bing Webmaster doğrulama',
            'body' => <<<'HTML'
                <p>Bing Webmaster Tools'da site sahipliğini doğrulamak için verilen meta etiketin (<code>msvalidate.01</code>) içeriği. Bing'in site verilerini açar.</p>
                HTML,
        ],
        'meta_pixel_id' => [
            'title' => 'Meta Pixel',
            'body' => <<<'HTML'
                <p>Facebook ve Instagram reklamlarının performansını ölçen ve yeniden hedeflemeye izin veren Meta Pixel kimliği (genelde 15 haneli sayı).</p>
                <p><strong>Nereden:</strong> Meta Events Manager → veri kaynağınız → kimlik üstte yazar. Reklam vermiyorsanız gerekmez.</p>
                HTML,
        ],
        'yandex_metrica_id' => [
            'title' => 'Yandex Metrica',
            'body' => <<<'HTML'
                <p>Yandex'in ücretsiz ziyaretçi analiz aracının sayaç numarası. Isı haritası, oturum kaydı gibi ek özellikler sunar. metrica.yandex.com'dan alınır.</p>
                HTML,
        ],
        'yandex_verification' => [
            'title' => 'Yandex Webmaster doğrulama',
            'body' => '<p>Yandex Webmaster'."'".'da site sahipliğini doğrulamak için verilen kod. Yandex arama sonuçları için site verilerini açar.</p>',
        ],
        'tiktok_pixel_id' => [
            'title' => 'TikTok Pixel',
            'body' => <<<'HTML'
                <p>TikTok reklamlarının dönüşümlerini ölçen Pixel kimliği. TikTok Ads Manager → Events Manager'dan alınır. Reklam vermiyorsanız boş bırakın.</p>
                HTML,
        ],
        'linkedin_partner_id' => [
            'title' => 'LinkedIn Insight',
            'body' => <<<'HTML'
                <p>LinkedIn reklamlarının (özellikle B2B) dönüşümlerini ölçen “Insight Tag” ortak kimliği — sayı. LinkedIn Campaign Manager → Hesap Varlıkları → Insight Tag'den alınır.</p>
                HTML,
        ],
        'head_scripts' => [
            'title' => 'Sayfa başına eklenecek kod',
            'body' => <<<'HTML'
                <p>Yukarıdaki listede olmayan bir izleme/doğrulama kodunu buraya olduğu gibi yapıştırın; her sayfanın <code>&lt;head&gt;</code> bölümüne eklenir.</p>
                <p><strong>Yalnızca güvendiğiniz kaynaklardan</strong> kod ekleyin — hatalı kod siteyi yavaşlatabilir veya bozabilir.</p>
                HTML,
        ],
        'body_scripts' => [
            'title' => 'Sayfa gövdesine eklenecek kod',
            'body' => <<<'HTML'
                <p>Bazı canlı destek ve analiz servisleri kodlarının sayfa gövdesinin (<code>&lt;body&gt;</code>) hemen başına konmasını ister. Öyle bir kod verildiyse buraya yapıştırın.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | İçerikler — Ayarlar › İçerikler
    |----------------------------------------------------------------------
    */
    'contents' => [
        'about_title' => [
            'title' => 'Hakkımızda başlığı',
            'body' => '<p>Kurumsal hakkımızda sayfasının başlığı — örn. “Hakkımızda” ya da “Biz Kimiz”.</p>',
        ],
        'about_content' => [
            'title' => 'Hakkımızda içeriği',
            'body' => <<<'HTML'
                <p>Hakkımızda sayfasının gövdesi: hikâyeniz, ekibiniz, değerleriniz, neden sizi seçmeliler.</p>
                <p>Somut olun — kuruluş yılı, tamamlanan proje sayısı, uzmanlık alanları güven verir.</p>
                HTML,
        ],
        'cookie_content' => [
            'title' => 'Çerez politikası metni',
            'body' => <<<'HTML'
                <p><code>/cerez-politikasi</code> sayfasında yayınlanan tam metin. Hangi çerezleri neden kullandığınızı, ne kadar sürdüklerini ve ziyaretçinin tercihlerini nasıl değiştirebileceğini anlatır.</p>
                HTML,
        ],
        'kvkk_content' => [
            'title' => 'KVKK aydınlatma metni',
            'body' => <<<'HTML'
                <p>Kişisel verilerin korunmasına dair aydınlatma metni. İletişim formundaki onay kutusu ve ilgili sayfa bu metne bağlanır.</p>
                <p>Hangi verileri (ad, e-posta, telefon) hangi amaçla topladığınızı, ne kadar sakladığınızı ve kişinin haklarını içermelidir. Hukuki metin olduğundan bir uzmana danışmanız önerilir.</p>
                HTML,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Yapay zeka sağlayıcı — /admin/ai-provider modalı
    |----------------------------------------------------------------------
    */
    'ai_provider' => [
        'driver' => [
            'title' => 'Servis',
            'body' => <<<'HTML'
                <p>Hangi yapay zeka servisini kullanacağınız: <strong>ChatGPT</strong> (OpenAI), <strong>DeepSeek</strong> ya da <strong>Ollama</strong> (kendi sunucunuzda çalışan yerel model).</p>
                <p>Seçince API adresi ve model alanları o servisin varsayılanlarıyla dolar.</p>
                HTML,
        ],
        'name' => [
            'title' => 'Ad',
            'body' => '<p>Bu kaydı panelde tanımanız için bir etiket — örn. “ChatGPT – Üretim”. İşleve etkisi yoktur.</p>',
        ],
        'model' => [
            'title' => 'Model',
            'body' => <<<'HTML'
                <p>Kullanılacak dil modelinin tam adı — örn. <code>gpt-4o-mini</code>, <code>deepseek-chat</code>.</p>
                <p>Daha güçlü modeller daha kaliteli ama daha pahalı ve yavaştır. Servis sağlayıcının belgelerindeki güncel model adını yazın.</p>
                HTML,
        ],
        'base_url' => [
            'title' => 'API adresi',
            'body' => <<<'HTML'
                <p>Servisin isteklerinizi kabul ettiği adres. Bilinen servisi seçtiğinizde otomatik dolar — Ollama gibi kendi sunucunuzdaysa kendi adresinizi yazın (örn. <code>http://localhost:11434/v1</code>).</p>
                HTML,
        ],
        'api_key' => [
            'title' => 'API anahtarı',
            'body' => <<<'HTML'
                <p>Servisin panelinden aldığınız gizli erişim anahtarı. Sunucuda <strong>şifrelenerek</strong> saklanır.</p>
                <p>Ollama gibi yerel modellerde genellikle gerekmez. Kayıtlıysa alan boş görünür; dokunmazsanız eski anahtar korunur.</p>
                HTML,
        ],
        'temperature' => [
            'title' => 'Sıcaklık',
            'body' => <<<'HTML'
                <p>Metnin ne kadar “yaratıcı” olacağını ayarlar. <code>0</code>'a yakın = tutarlı ve öngörülebilir; <code>1</code> ve üstü = daha çeşitli ama daha savruk.</p>
                <p>İçerik üretimi için <strong>0.6–0.8</strong> arası iyi bir başlangıçtır.</p>
                HTML,
        ],
        'max_tokens' => [
            'title' => 'Maksimum token',
            'body' => <<<'HTML'
                <p>Tek bir üretimde modelin verebileceği en fazla metin miktarı (token ≈ kelime parçası; 1000 token ≈ 750 kelime).</p>
                <p>Uzun blog yazıları için <strong>4000</strong> civarı uygundur. Çok yüksek değer maliyeti artırır.</p>
                HTML,
        ],
        'timeout' => [
            'title' => 'Zaman aşımı (saniye)',
            'body' => <<<'HTML'
                <p>Modelden yanıt için en fazla kaç saniye beklenecek. Uzun üretimler zaman aldığından <strong>120–180</strong> saniye önerilir; süre dolarsa üretim başarısız işaretlenir.</p>
                HTML,
        ],
        'json_mode' => [
            'title' => 'JSON modu',
            'body' => <<<'HTML'
                <p>Açıkken modelden yanıtı düzenli bir <strong>JSON</strong> yapısında vermesi istenir; panel bu yapıyı ayrıştırıp alanları (başlık, özet, içerik…) otomatik doldurur.</p>
                <p>Destekleyen modellerde açık bırakın. Bazı yerel modeller desteklemez.</p>
                HTML,
        ],
        'is_active' => [
            'title' => 'Aktif',
            'body' => '<p>Kapalıyken bu sağlayıcı üretimde kullanılmaz. Anahtarı geçici olarak devre dışı bırakmak için.</p>',
        ],
        'is_default' => [
            'title' => 'Varsayılan',
            'body' => '<p>Bir prompt şablonunda sağlayıcı seçilmemişse bu sağlayıcı kullanılır. Yalnızca biri varsayılan olabilir.</p>',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Yapay zeka prompt şablonu — /admin/ai-prompt modalı
    |----------------------------------------------------------------------
    */
    'ai_prompt' => [
        'name' => [
            'title' => 'Şablon adı',
            'body' => '<p>Şablonu listede tanımanız için bir ad — örn. “Detaylı rehber yazısı”.</p>',
        ],
        'key' => [
            'title' => 'Modül anahtarı',
            'body' => <<<'HTML'
                <p>Bu şablonun <strong>hangi ekranda</strong> görüneceğini belirler. Sistem belirli anahtarları arar:</p>
                <ul>
                    <li><code>blog.content</code> → blog yazısı formundaki “Yapay Zeka ile Oluştur”</li>
                    <li><code>service.content</code> → hizmet formu</li>
                    <li><code>page.content</code> → sayfa formu</li>
                </ul>
                <p>Aynı anahtara birden çok şablon tanımlayıp üretim sırasında seçtirebilirsiniz.</p>
                HTML,
        ],
        'ai_provider_id' => [
            'title' => 'Sağlayıcı',
            'body' => '<p>Bu şablonun hangi yapay zeka servisini kullanacağı. Boş bırakırsanız varsayılan sağlayıcı kullanılır.</p>',
        ],
        'system_prompt' => [
            'title' => 'Sistem prompt',
            'body' => <<<'HTML'
                <p>Modele <strong>kim olduğunu ve nasıl davranacağını</strong> söyleyen talimat. Ziyaretçi görmez.</p>
                <p>Örn. “Sen deneyimli bir Türkçe içerik editörüsün. SEO uyumlu, akıcı ve özgün metin üretirsin. Çıktıyı şu JSON anahtarlarıyla ver: title, excerpt, content…”</p>
                HTML,
        ],
        'user_prompt' => [
            'title' => 'Kullanıcı prompt',
            'body' => <<<'HTML'
                <p>Her üretimde modele gönderilen asıl istek. Şu değişkenleri kullanabilirsiniz; doldurulmayanlar otomatik silinir:</p>
                <ul>
                    <li><code>@{{keywords}}</code> anahtar kelimeler</li>
                    <li><code>@{{title}}</code> başlık (boş olabilir)</li>
                    <li><code>@{{category}}</code> kategori · <code>@{{length}}</code> uzunluk · <code>@{{notes}}</code> ek notlar</li>
                </ul>
                HTML,
        ],
        'is_active' => [
            'title' => 'Aktif',
            'body' => '<p>Kapalıyken bu şablon üretim ekranında seçeneklerde görünmez.</p>',
        ],
        'is_default' => [
            'title' => 'Varsayılan',
            'body' => '<p>Aynı modül anahtarına birden çok şablon varsa, üretim modalında önce bu seçili gelir.</p>',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Küçük modüller
    |----------------------------------------------------------------------
    */
    'faq' => [
        'is_active' => [
            'title' => 'Yayında',
            'body' => '<p>Kapatırsanız soru sitede görünmez ama panelde durur. Mevsimlik ya da artık geçerli olmayan soruları silmek yerine kapatın.</p>',
        ],
        'question' => [
            'title' => 'Soru',
            'body' => <<<'HTML'
                <p>Müşterinin gerçekten sorduğu soruyu, onun kelimeleriyle yazın — örn. “Web sitesi ne kadar sürede teslim edilir?”.</p>
                <p>Sorular ayrı bir havuzda tutulur ve blog/hizmet/sayfa içeriklerine bağlanabilir; Google'da genişletilmiş sonuç olarak çıkabilir.</p>
                HTML,
        ],
        'answer' => [
            'title' => 'Cevap',
            'body' => <<<'HTML'
                <p>Kısa, net ve doğrudan cevap verin (2–4 cümle). Belirsiz ifadelerden kaçının; mümkünse somut süre/rakam verin.</p>
                HTML,
        ],
    ],

    'reference' => [
        'is_active' => [
            'title' => 'Yayında',
            'body' => '<p>Kapatırsanız logo “Referanslarımız” bölümünde görünmez. Sözleşmesi biten firmaları silmek yerine kapatabilirsiniz.</p>',
        ],
        'logo_media_id' => [
            'title' => 'Firma logosu',
            'body' => <<<'HTML'
                <p>Birlikte çalıştığınız firmanın logosu. “Referanslarımız” bölümünde gösterilir.</p>
                <p>Kırpma yapılmaz, logo kendi oranında yüklenir. Şeffaf zeminli PNG en iyisidir. Logoyu kullanma izniniz olduğundan emin olun.</p>
                HTML,
        ],
        'name' => [
            'title' => 'Firma adı',
            'body' => '<p>Referans firmanın adı. Logonun üstüne gelince ipucu olarak ve erişilebilirlik için kullanılır.</p>',
        ],
        'url' => [
            'title' => 'Bağlantı',
            'body' => '<p>Firmanın web sitesi. Girilirse logo tıklanabilir olur. İsteğe bağlı.</p>',
        ],
    ],

    'testimonial' => [
        'is_active' => [
            'title' => 'Yayında',
            'body' => '<p>Kapatırsanız yorum sitede görünmez. Kaldırılmasını isteyen müşterinin yorumunu silmek yerine kapatın.</p>',
        ],
        'photo_media_id' => [
            'title' => 'Fotoğraf',
            'body' => '<p>Yorumu yapan kişinin fotoğrafı. Gerçek bir yüz güveni artırır. Yoksa baş harfleri gösterilir.</p>',
        ],
        'name' => [
            'title' => 'İsim',
            'body' => '<p>Yorumu yapan kişinin adı soyadı. Gerçek isim kullanın; “A.Y.” gibi kısaltmalar güveni azaltır.</p>',
        ],
        'title' => [
            'title' => 'Unvan',
            'body' => '<p>Kişinin görevi ve firması — örn. “Pazarlama Müdürü, BrightEdge Media”. Yorumun ağırlığını artırır.</p>',
        ],
        'content' => [
            'title' => 'Yorum',
            'body' => <<<'HTML'
                <p>Müşterinin kendi ifadesi. Somut sonuç içeren yorumlar (“3 ayda talepler iki katına çıktı”) genel övgülerden daha etkilidir.</p>
                <p>İzin almadan gerçek yorumları yayınlamayın.</p>
                HTML,
        ],
        'rating' => [
            'title' => 'Puan',
            'body' => '<p>1–5 yıldız. Yorum kartında yıldız olarak gösterilir.</p>',
        ],
    ],

    'why_choose_us' => [
        'is_active' => [
            'title' => 'Yayında',
            'body' => '<p>Kapatırsanız bu madde sitede görünmez. Kampanya dönemine özel maddeleri böyle saklayabilirsiniz.</p>',
        ],
        'title' => [
            'title' => 'Başlık',
            'body' => '<p>Bir avantajınızı özetleyen kısa başlık — örn. “7/24 Destek” ya da “Zamanında Teslim”.</p>',
        ],
        'description' => [
            'title' => 'Açıklama',
            'body' => '<p>Başlığı bir-iki cümleyle açan metin. Vaadi somutlaştırın: nasıl, ne kadar sürede, hangi güvenceyle.</p>',
        ],
    ],

    'service_region' => [
        'parent_id' => [
            'title' => 'Üst bölge',
            'body' => <<<'HTML'
                <p>Bu kaydın hangi bölgenin altına gireceği. <strong>Boş bırakılırsa</strong> kayıt bir <em>il</em> olur; bir il seçilirse <em>ilçe</em> olur.</p>
                <p>Adres buna göre oluşur: <code>/gaziantep</code> (il) → <code>/gaziantep/sahinbey</code> (ilçe).</p>
                HTML,
        ],
        'name' => [
            'title' => 'Bölge adı',
            'body' => '<p>İlin ya da ilçenin adı — örn. “Gaziantep”, “Şahinbey”. Hizmet metinlerindeki yer tutucular bu adla değişir.</p>',
        ],
        'slug' => [
            'title' => 'Kısa ad (slug)',
            'body' => '<p>Bölgenin adreste görünen hali. Boş bırakırsanız addan üretilir (Şahinbey → <code>sahinbey</code>).</p>',
        ],
        'description' => [
            'title' => 'Bölgeye özel metin',
            'body' => <<<'HTML'
                <p>Bu bölgenin hizmet sayfalarına eklenecek <strong>özgün</strong> metin. Aynı hizmetin farklı bölge sayfalarının birbirinin kopyası olmaması için önemlidir — Google kopya içeriği sevmez.</p>
                <p>Bölgeye dair gerçek bilgi yazın: hizmet verdiğiniz mahalleler, o bölgedeki referanslarınız, yerel ihtiyaçlar.</p>
                HTML,
        ],
    ],

    'blog_category' => [
        'name' => [
            'title' => 'Kategori adı',
            'body' => '<p>Blog konularınızdan biri — örn. “Web Tasarım”, “Dijital Pazarlama”. Blog menüsünde ve yazı kartlarında görünür.</p>',
        ],
        'slug' => [
            'title' => 'Kısa ad (slug)',
            'body' => '<p>Kategorinin adreste görünen hali. Boş bırakırsanız addan üretilir.</p>',
        ],
        'description' => [
            'title' => 'Açıklama',
            'body' => '<p>Kategori sayfasının üstünde görünen tanıtım metni. Hem ziyaretçiye yol gösterir hem aramalara katkı sağlar.</p>',
        ],
    ],

    'social_link' => [
        'name' => [
            'title' => 'Ad',
            'body' => '<p>Platformun adı — örn. “Instagram”, “LinkedIn”. İkonun yanında/altında ve erişilebilirlik etiketinde kullanılır.</p>',
        ],
        'url' => [
            'title' => 'Bağlantı',
            'body' => '<p>Profilinizin tam adresi — örn. <code>https://instagram.com/firmaniz</code>. Bu adresler yapılandırılmış veriye de otomatik eklenir.</p>',
        ],
        'icon_media_id' => [
            'title' => 'İkon',
            'body' => '<p>Platformun ikonu. Altbilgide ve iletişim alanında gösterilir. Tek renk, şeffaf zeminli SVG/PNG önerilir.</p>',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Tanıtım alanı (hero) — /admin/hero
    |----------------------------------------------------------------------
    */
    'hero' => [
        'badge' => [
            'title' => 'Üst etiket',
            'body' => '<p>Ana başlığın hemen üstündeki küçük etiket — örn. “Dijital Ajans” ya da “2015'."'".'ten beri”. Kısa tutun; boş da bırakabilirsiniz.</p>',
        ],
        'title' => [
            'title' => 'Başlık',
            'body' => <<<'HTML'
                <p>Ana sayfada ziyaretçinin ilk gördüğü büyük cümle. Ne yaptığınızı ve faydayı net söyleyin — örn. “Markanızı dijitalde büyütüyoruz”.</p>
                HTML,
        ],
        'description' => [
            'title' => 'Açıklama',
            'body' => '<p>Başlığın altındaki 1–2 cümlelik destekleyici metin. Kime, hangi sonucu sunduğunuzu açar.</p>',
        ],
        'button_text' => [
            'title' => 'Buton yazısı',
            'body' => '<p>Eylem çağrısı düğmesinin yazısı — örn. “Ücretsiz Teklif Alın”, “Projelerimizi İnceleyin”.</p>',
        ],
        'button_url' => [
            'title' => 'Buton bağlantısı',
            'body' => <<<'HTML'
                <p>Düğmenin gideceği adres. Kendi sitenizde bir sayfa için <code>/iletisim</code>, dış bağlantı için tam adres.</p>
                HTML,
        ],
        'gallery_media_ids' => [
            'title' => 'Görseller',
            'body' => '<p>Tanıtım alanında dönen/sıralanan görseller. Birden fazla ekleyebilir, yıldız ile kapak (ilk) görseli seçebilirsiniz.</p>',
        ],
        'background_media_id' => [
            'title' => 'Arka plan görseli',
            'body' => <<<'HTML'
                <p>Tanıtım bölümünün arkasına yerleşen büyük görsel. Üstüne yazı geleceği için sakin, çok detaysız bir görsel seçin; koyu bir katman eklenebilir.</p>
                HTML,
        ],
    ],
];
