/**
 * Canlı SEO analizi — <x-admin::form.seo> içindeki [data-seo-analysis] panelini
 * doldurur. Kural mantığı App\Services\Seo\SeoAnalyzer (PHP) ile birebir aynı;
 * eşikler data-seo-rules (config/seo.php) üzerinden gelir. Sunucu kaydederken
 * skoru yeniden hesaplar — burası yalnızca anlık geri bildirim.
 */

const VOWELS = /[aeıioöuüâîû]/giu;
const STATUS = {
    good: { dot: 'bg-success-500', icon: 'check_circle', text: 'text-black dark:text-white' },
    ok: { dot: 'bg-warning-500', icon: 'error', text: 'text-black dark:text-white' },
    bad: { dot: 'bg-danger-500', icon: 'cancel', text: 'text-black dark:text-white' },
    na: { dot: 'bg-gray-300 dark:bg-[#172036]', icon: 'remove', text: 'text-gray-400' },
};
const RING = { good: '#22c55e', ok: '#f59e0b', bad: '#ef4444' };

const TR_MAP = { ç: 'c', ğ: 'g', ı: 'i', ö: 'o', ş: 's', ü: 'u' };
const slugify = (v) => v.toLowerCase().replace(/[çğıöşü]/g, (c) => TR_MAP[c] ?? c)
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

const norm = (v) => (v || '').toLowerCase().replace(/\s+/g, ' ').trim();
const words = (t) => norm(t).split(/[^\p{L}\p{N}]+/u).filter(Boolean);
const sentences = (t) => t.trim().split(/(?<=[.!?…])\s+/u).filter((s) => words(s).length >= 2);

function plainText(html) {
    const el = document.createElement('div');
    el.innerHTML = html.replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, ' ');
    return norm(el.textContent || '');
}

function collect(root) {
    const form = root.closest('form') ?? document;
    const html = form.querySelector(`[name="${CSS.escape(root.dataset.seoContentSource || 'content')}"]`)?.value ?? '';
    const container = document.createElement('div');
    container.innerHTML = html;

    return {
        keyword: norm(root.querySelector('[data-seo-input="focus_keyword"]')?.value ?? ''),
        title: resolve(root, 'meta_title', root.dataset.seoTitleSource),
        description: resolve(root, 'meta_description', root.dataset.seoDescriptionSource),
        slug: value(root, root.dataset.seoSlugSource),
        html,
        container,
        host: root.dataset.seoHost || '',
        type: root.dataset.seoType || 'default',
    };
}

function value(root, name) {
    const form = root.closest('form') ?? document;
    return (name ? form.querySelector(`[name="${CSS.escape(name)}"]`)?.value : '') ?? '';
}

function resolve(root, metaKey, sourceName) {
    const meta = root.querySelector(`[data-seo-input="${metaKey}"]`)?.value?.trim();
    return meta || value(root, sourceName).trim();
}

/* ---------------------------------------------------------------- */

function analyze(input, rules) {
    const { keyword: kw, title, description, slug, html, container, host, type } = input;
    const text = plainText(html);
    const w = words(text);
    const wc = w.length;
    const sents = sentences(text);
    const paragraphs = [...container.querySelectorAll('p')].map((p) => plainText(p.innerHTML)).filter(Boolean);
    const headings = [...container.querySelectorAll('h2,h3,h4,h5,h6')].map((h) => norm(h.textContent)).filter(Boolean);
    const links = [...container.querySelectorAll('a[href]')].map((a) => {
        const href = a.getAttribute('href') || '';
        return { href, internal: !href.includes('://') || (host && href.includes(host)) };
    }).filter((l) => l.href && !l.href.startsWith('#'));
    const images = [...container.querySelectorAll('img')].map((i) => ({ alt: i.getAttribute('alt') || '' }));
    const intro = norm(w.slice(0, rules.first_paragraph_words).join(' '));
    const has = kw !== '';

    const R = [];
    const row = (id, label, status, txt) => R.push({ id, label, status, text: txt });
    const kwRow = (id, label, pass, good, bad) => row(id, label, has ? (pass ? 'good' : 'bad') : 'na',
        has ? (pass ? good : bad) : 'Önce bir odak anahtar kelime belirleyin.');

    row('keyword_set', 'Odak anahtar kelime', has ? 'good' : 'bad',
        has ? `Odak kelime belirlendi: “${kw}”.` : 'Bir odak anahtar kelime belirleyin — analiz buna göre yapılır.');
    kwRow('keyword_in_title', 'Anahtar kelime meta başlıkta', norm(title).includes(kw),
        'Meta başlık odak kelimeyi içeriyor.', 'Meta başlıkta odak kelime geçmiyor.');
    kwRow('keyword_in_description', 'Anahtar kelime meta açıklamada', norm(description).includes(kw),
        'Meta açıklama odak kelimeyi içeriyor.', 'Meta açıklamada odak kelime geçmiyor.');
    kwRow('keyword_in_slug', 'Anahtar kelime adreste (slug)', slug !== '' && slugify(slug).includes(slugify(kw)),
        'Adres odak kelimeyi içeriyor.', 'Sayfa adresinde odak kelime geçmiyor.');
    kwRow('keyword_in_intro', 'Anahtar kelime ilk paragrafta', intro.includes(kw),
        'Odak kelime girişte geçiyor.', 'Odak kelimeyi ilk paragrafta kullanın.');
    kwRow('keyword_in_subheading', 'Anahtar kelime bir ara başlıkta', headings.some((h) => h.includes(kw)),
        'En az bir H2/H3 odak kelimeyi içeriyor.', 'Ara başlıkların hiçbirinde odak kelime yok.');

    if (!has || images.length === 0) {
        row('keyword_in_image_alt', 'Anahtar kelime görsel alt metninde', 'na',
            images.length === 0 ? 'İçerikte görsel yok.' : 'Önce odak kelime belirleyin.');
    } else {
        const hit = images.some((i) => norm(i.alt).includes(kw));
        row('keyword_in_image_alt', 'Anahtar kelime görsel alt metninde', hit ? 'good' : 'ok',
            hit ? 'En az bir görselin alt metni odak kelimeyi içeriyor.' : 'Görsel alt metinlerinde odak kelime geçmiyor.');
    }

    density(R, has, kw, w, wc, rules.density);
    length(R, 'title_length', 'Meta başlık uzunluğu', [...title].length, rules.title, 'karakter');
    length(R, 'description_length', 'Meta açıklama uzunluğu', [...description].length, rules.description, 'karakter');

    const minWords = rules.min_words[type] ?? rules.min_words.default;
    row('content_length', 'İçerik uzunluğu',
        wc >= minWords ? 'good' : wc >= Math.round(minWords * 0.6) ? 'ok' : 'bad',
        wc >= minWords ? `İçerik ${wc} kelime — yeterli.` : `İçerik ${wc} kelime (en az ${minWords} önerilir).`);

    if (images.length === 0) {
        row('images_have_alt', 'Görsel alt metinleri', 'na', 'İçerikte görsel yok.');
    } else {
        const missing = images.filter((i) => !i.alt.trim()).length;
        row('images_have_alt', 'Görsel alt metinleri', missing === 0 ? 'good' : missing < images.length ? 'ok' : 'bad',
            missing === 0 ? `${images.length} görselin tamamında alt metni var.` : `${missing}/${images.length} görselde alt metni eksik.`);
    }

    linkRow(R, 'internal_links', 'İç bağlantılar', links.filter((l) => l.internal).length, wc,
        (n) => `${n} iç bağlantı var.`, 'Sitedeki başka sayfalara bağlantı verin.', 'bad');
    linkRow(R, 'outbound_links', 'Dış bağlantılar', links.filter((l) => !l.internal).length, wc,
        (n) => `${n} dış bağlantı var.`, 'Güvenilir dış kaynaklara bağlantı vermeyi düşünün.', 'ok');

    if (paragraphs.length === 0) {
        row('paragraph_length', 'Paragraf uzunluğu', 'na', 'Paragraf bulunamadı.');
    } else {
        const long = paragraphs.filter((p) => words(p).length > rules.paragraph_max_words).length;
        row('paragraph_length', 'Paragraf uzunluğu', long === 0 ? 'good' : 'bad',
            long === 0 ? 'Paragraflar makul uzunlukta.' : `${long} paragraf ${rules.paragraph_max_words} kelimeden uzun — bölün.`);
    }

    if (sents.length < 3) {
        row('sentence_length', 'Cümle uzunluğu', 'na', 'Değerlendirmek için çok az cümle.');
    } else {
        const long = sents.filter((s) => words(s).length > rules.sentence_long_words).length;
        const ratio = Math.round(long / sents.length * 100);
        const th = rules.sentence_long_ratio;
        row('sentence_length', 'Cümle uzunluğu', ratio <= th ? 'good' : ratio <= th * 1.6 ? 'ok' : 'bad',
            ratio <= th ? `Uzun cümle oranı %${ratio} — iyi.` : `Cümlelerin %${ratio}'i ${rules.sentence_long_words} kelimeden uzun (hedef ≤ %${th}).`);
    }

    subheadings(R, container, wc, headings, rules.section_max_words);

    const read = readability(w, sents, rules);
    R.push(read.check);

    const score = scoreOf(R, rules.weights);
    return { score, grade: gradeOf(score, rules.grade), readability: read.score, readability_label: read.label, checks: R };
}

function density(R, has, kw, w, wc, range) {
    if (!has || wc < 50) {
        R.push({ id: 'keyword_density', label: 'Anahtar kelime yoğunluğu', status: 'na',
            text: wc < 50 ? 'Yoğunluk için içerik çok kısa.' : 'Önce odak kelime belirleyin.' });
        return;
    }
    const parts = words(kw);
    let occ = 0;
    for (let i = 0; i <= w.length - parts.length; i++) {
        if (parts.every((p, j) => w[i + j] === p)) occ++;
    }
    const d = Math.round(occ * Math.max(parts.length, 1) / wc * 1000) / 10;
    let status; let text;
    if (d >= range.min && d <= range.max) { status = 'good'; text = `Yoğunluk %${d} — ideal aralıkta (${occ} kez).`; }
    else if (d > 0 && d < range.min) { status = 'ok'; text = `Yoğunluk %${d} — biraz düşük (ideal %${range.min}–%${range.max}).`; }
    else if (d > range.max && d <= range.max * 2) { status = 'ok'; text = `Yoğunluk %${d} — biraz yüksek (ideal %${range.min}–%${range.max}).`; }
    else { status = 'bad'; text = d === 0 ? 'Odak kelime içerikte hiç geçmiyor.' : `Yoğunluk %${d} — aşırı (keyword stuffing riski).`; }
    R.push({ id: 'keyword_density', label: 'Anahtar kelime yoğunluğu', status, text });
}

function length(R, id, label, len, range, unit) {
    let status; let text;
    if (len === 0) { status = 'bad'; text = `Boş — ${label.toLowerCase()} girin.`; }
    else if (len >= range.min && len <= range.max) { status = 'good'; text = `${label} ideal uzunlukta.`; }
    else if (len >= range.min - 5 && len <= range.max + 10) { status = 'ok'; text = `${len} ${unit} (ideal ${range.min}–${range.max}).`; }
    else { status = 'bad'; text = `${len} ${unit} (ideal ${range.min}–${range.max}).`; }
    R.push({ id, label, status, text });
}

function linkRow(R, id, label, count, wc, goodFn, emptyText, emptyStatus) {
    if (wc < 50) { R.push({ id, label, status: 'na', text: 'İçerik çok kısa.' }); return; }
    R.push({ id, label, status: count > 0 ? 'good' : emptyStatus, text: count > 0 ? goodFn(count) : emptyText });
}

function subheadings(R, container, wc, headings, max) {
    if (wc < max) { R.push({ id: 'subheading_distribution', label: 'Ara başlık dağılımı', status: 'na', text: 'İçerik kısa, ara başlık şart değil.' }); return; }
    if (headings.length === 0) { R.push({ id: 'subheading_distribution', label: 'Ara başlık dağılımı', status: 'bad', text: 'Uzun içerikte hiç ara başlık yok — H2/H3 ekleyin.' }); return; }
    const html = container.innerHTML;
    const longest = Math.max(...html.split(/<h[2-6][^>]*>[\s\S]*?<\/h[2-6]>/i).map((c) => words(plainText(c)).length));
    R.push({ id: 'subheading_distribution', label: 'Ara başlık dağılımı', status: longest <= max ? 'good' : 'ok',
        text: longest <= max ? `${headings.length} ara başlık, bölümler dengeli.` : `Bir bölüm ${longest} kelime — araya başlık ekleyin.` });
}

function readability(w, sents, rules) {
    if (w.length < 30) {
        return { score: null, label: '—', check: { id: 'readability', label: 'Okunabilirlik (Ateşman)', status: 'na', text: 'Puan için içerik çok kısa.' } };
    }
    let syl = 0;
    w.forEach((word) => { syl += Math.max((word.match(VOWELS) || []).length, 1); });
    const raw = 198.825 - 40.175 * (syl / w.length) - 2.610 * (w.length / Math.max(sents.length, 1));
    const score = Math.round(Math.max(0, Math.min(100, raw)));
    const band = rules.readability_bands.find((b) => score >= b.min);
    const label = band ? band.label : '—';
    const status = score >= rules.readability.good ? 'good' : score >= rules.readability.ok ? 'ok' : 'bad';
    return { score, label, check: { id: 'readability', label: 'Okunabilirlik (Ateşman)', status, text: `Ateşman puanı ${score} — ${label}.` } };
}

function scoreOf(checks, weights) {
    let earned = 0; let possible = 0;
    checks.forEach((c) => {
        if (c.status === 'na') return;
        const wgt = weights[c.id] ?? 1;
        possible += wgt;
        earned += c.status === 'good' ? wgt : c.status === 'ok' ? wgt * 0.5 : 0;
    });
    return possible > 0 ? Math.round(earned / possible * 100) : 0;
}

const gradeOf = (score, g) => score <= g.bad ? 'bad' : score <= g.ok ? 'ok' : 'good';

/* ---------------------------------------------------------------- */

class SeoAnalyzerPanel {
    constructor(root) {
        this.root = root;
        this.panel = root.querySelector('[data-seo-analysis]');
        this.rules = JSON.parse(root.dataset.seoRules || '{}');
        this.expanded = false;

        this.panel.querySelector('[data-seo-toggle]')?.addEventListener('click', () => {
            this.expanded = !this.expanded;
            this.render();
        });

        const run = debounce(() => this.render(), 250);
        const form = root.closest('form') ?? document;
        ['input', 'change'].forEach((ev) => form.addEventListener(ev, run));
        document.getElementById('light-dark-toggle')?.addEventListener('click', () => setTimeout(() => this.render(), 50));

        this.render();
    }

    render() {
        this.report = analyze(collect(this.root), this.rules);
        const { score, grade, readability, readability_label: rl, checks } = this.report;

        const ring = this.panel.querySelector('[data-seo-ring]');
        ring.setAttribute('stroke-dasharray', `${score} 100`);
        ring.style.stroke = RING[grade];
        this.panel.querySelector('[data-seo-score]').textContent = score;

        const grades = { bad: 'Kötü', ok: 'İyileştirilebilir', good: 'İyi' };
        this.panel.querySelector('[data-seo-grade]').textContent = `SEO: ${grades[grade]} (${score}/100)`;
        this.panel.querySelector('[data-seo-readability]').textContent =
            readability === null ? '' : `Okunabilirlik: ${rl} (${readability}/100)`;

        const shown = this.expanded ? checks : checks.filter((c) => c.status === 'bad' || c.status === 'ok');
        const list = shown.length ? shown : checks.filter((c) => c.status === 'good').slice(0, 3);

        this.panel.querySelector('[data-seo-toggle]').textContent =
            this.expanded ? 'sadece sorunlar' : `tümünü göster (${checks.length})`;

        this.panel.querySelector('[data-seo-checks]').innerHTML = list.map((c) => {
            const s = STATUS[c.status];
            return `<li class="flex items-start gap-[8px] ${s.text}">
                <span class="mt-[5px] w-[8px] h-[8px] rounded-full shrink-0 ${s.dot}"></span>
                <span><strong class="font-medium">${escape(c.label)}:</strong> ${escape(c.text)}</span>
            </li>`;
        }).join('') || '<li class="text-gray-400">İçerik girildikçe kontroller burada listelenir.</li>';
    }
}

function escape(v) {
    return String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
}

function debounce(fn, ms) {
    let t;
    return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
}

export function initSeoAnalyzers(root = document) {
    root.querySelectorAll('[data-seo][data-seo-rules]:not([data-seo-analyzer-ready])').forEach((el) => {
        el.dataset.seoAnalyzerReady = '1';
        new SeoAnalyzerPanel(el);
    });
}

document.addEventListener('DOMContentLoaded', () => initSeoAnalyzers());
document.addEventListener('admin:content-loaded', (event) => initSeoAnalyzers(event.target));
