/**
 * Denetim kaydı arayüzünün paylaşılan parçaları.
 *
 * Üç yerde kullanılır:
 *   1. /admin/activity-log merkezi sayfası (pages/activity-log/index.js)
 *   2. Modül index sayfalarındaki "Log Kayıtları" butonu -> openLogList()
 *   3. Satır menüsündeki "Geçmiş" -> openLogList({subjectType, subjectId})
 *
 * Satır şablonu ve detay modalı tek yerde durur; üç kullanım da aynı
 * görünümü paylaşır.
 */

import { escapeHtml, http, HttpError } from './http.js';

/* ------------------------------------------------------------------ *
 * Rozetler ve hücreler
 * ------------------------------------------------------------------ */

/** Karşılığı olan SVG yoksa marka renkli harf rozeti — hiçbir zaman boş kalmaz. */
const BRAND_COLORS = {
    'Microsoft Edge': '#0078D7',
    'Internet Explorer': '#0076D6',
};

function lettermark(label) {
    const color = BRAND_COLORS[label] ?? '#8695aa';
    const letter = (label ?? '?').trim().charAt(0).toUpperCase();

    return `<span class="w-[18px] h-[18px] shrink-0 rounded-[5px] inline-flex items-center justify-center text-[10px] font-bold text-white"
        style="background:${color}">${escapeHtml(letter)}</span>`;
}

function brandIcon(iconUrl, label) {
    if (! label) {
        return '';
    }

    return iconUrl
        ? `<img src="${escapeHtml(iconUrl)}" alt="${escapeHtml(label)}" class="w-[18px] h-[18px] shrink-0 object-contain">`
        : lettermark(label);
}

/**
 * Olay rozeti renkleri. Sınıf adları BİLEREK tam yazılı: Tailwind kaynak
 * taraması statiktir, `bg-${color}-100` gibi çalışma anında birleştirilen
 * adları göremez ve o sınıflar sessizce derlenmez. Config'ten gelen renk
 * adı burada tam sınıf dizisine eşlenir.
 */
const BADGE_CLASSES = {
    primary: 'bg-primary-100 text-primary-600',
    secondary: 'bg-secondary-100 text-secondary-600',
    success: 'bg-success-100 text-success-600',
    danger: 'bg-danger-100 text-danger-600',
    warning: 'bg-warning-100 text-warning-600',
    info: 'bg-info-100 text-info-600',
    purple: 'bg-purple-100 text-purple-600',
    orange: 'bg-orange-100 text-orange-600',
    gray: 'bg-gray-100 text-gray-600',
};

/** Olay rozeti — renk ve ikon config/activity-log.php'den gelir. */
export function eventBadge(item) {
    const meta = item.event_meta ?? {};
    const classes = BADGE_CLASSES[meta.color] ?? BADGE_CLASSES.gray;

    return `<span class="inline-flex items-center gap-[5px] py-[4px] px-[9px] rounded-[7px] text-xs font-medium whitespace-nowrap ${classes} dark:bg-[#15203c]">
        <i class="material-symbols-outlined !text-[15px]">${escapeHtml(meta.icon ?? 'bolt')}</i>
        ${escapeHtml(meta.label ?? item.event)}
    </span>`;
}

/** Tarayıcı + işletim sistemi + cihaz tipi, ikonlarıyla. */
export function deviceCell(item) {
    const bits = [];

    if (item.browser) {
        bits.push(`<span class="inline-flex items-center gap-[5px]" title="${escapeHtml(item.browser)} ${escapeHtml(item.browser_version ?? '')}">
            ${brandIcon(item.browser_icon, item.browser)}
            <span class="text-xs">${escapeHtml(item.browser)}${item.browser_version ? ' ' + escapeHtml(item.browser_version) : ''}</span>
        </span>`);
    }

    if (item.platform) {
        bits.push(`<span class="inline-flex items-center gap-[5px]" title="${escapeHtml(item.platform)} ${escapeHtml(item.platform_version ?? '')}">
            ${brandIcon(item.platform_icon, item.platform)}
            <span class="text-xs">${escapeHtml(item.platform)}${item.platform_version ? ' ' + escapeHtml(item.platform_version) : ''}</span>
        </span>`);
    }

    if (bits.length === 0) {
        // Konsoldan (artisan/kuyruk) gelen kayıtlarda tarayıcı bilgisi yoktur.
        return '<span class="text-xs text-gray-500 dark:text-gray-400 inline-flex items-center gap-[5px]">'
            + '<i class="material-symbols-outlined !text-[16px]">terminal</i> Konsol</span>';
    }

    const location = item.location
        ? `<span class="inline-flex items-center gap-[4px] text-xs text-gray-500 dark:text-gray-400">
               <i class="material-symbols-outlined !text-[14px]">location_on</i>${escapeHtml(item.location)}
           </span>`
        : '';

    return `<div class="flex flex-col gap-[3px]">
        <div class="flex items-center gap-[10px] flex-wrap">${bits.join('')}</div>
        <div class="flex items-center gap-[10px] flex-wrap">
            <span class="inline-flex items-center gap-[4px] text-xs text-gray-500 dark:text-gray-400">
                <i class="material-symbols-outlined !text-[14px]">${escapeHtml(item.device_icon ?? 'devices_other')}</i>
                ${escapeHtml(item.ip_address ?? '—')}
            </span>
            ${location}
        </div>
    </div>`;
}

/** Kullanıcı hücresi — baş harf avatarı + ad + e-posta. */
export function causerCell(item) {
    if (! item.causer_name) {
        return '<span class="text-xs text-gray-500 dark:text-gray-400">Sistem / oturumsuz</span>';
    }

    const initials = item.causer_name.trim().split(/\s+/).slice(0, 2).map((p) => p.charAt(0)).join('').toUpperCase();

    return `<div class="flex items-center gap-[9px]">
        <span class="w-[30px] h-[30px] shrink-0 rounded-full bg-primary-100 dark:bg-[#15203c] text-primary-500 inline-flex items-center justify-center text-[11px] font-bold">
            ${escapeHtml(initials)}
        </span>
        <div class="min-w-0">
            <span class="block text-sm truncate">${escapeHtml(item.causer_name)}</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">${escapeHtml(item.causer_email ?? '')}</span>
        </div>
    </div>`;
}

const CELL = 'ltr:text-left rtl:text-right whitespace-nowrap px-[20px] py-[13px] border-b border-gray-100 dark:border-[#172036]';

/** Log tablosunun tek satırı — üç kullanım da bunu paylaşır. */
export function logRow(item, { showModule = true } = {}) {
    const critical = item.severity === 'critical';

    const subject = item.subject_label
        ? `<span class="block text-sm truncate max-w-[240px]" title="${escapeHtml(item.subject_label)}">${escapeHtml(item.subject_label)}</span>`
        : '<span class="block text-sm text-gray-500 dark:text-gray-400">—</span>';

    const module = showModule
        ? `<span class="inline-flex items-center gap-[4px] text-xs text-gray-500 dark:text-gray-400 mt-[2px]">
               <i class="material-symbols-outlined !text-[14px]">${escapeHtml(item.module_meta?.icon ?? 'category')}</i>
               ${escapeHtml(item.module_meta?.label ?? item.module)}
           </span>`
        : '';

    return `<tr class="${critical ? 'bg-danger-50/40 dark:bg-danger-500/5' : ''}">
        <td class="${CELL}">${eventBadge(item)}</td>
        <td class="${CELL}">
            <div class="flex flex-col">${subject}${module}</div>
        </td>
        <td class="${CELL}">${causerCell(item)}</td>
        <td class="${CELL}">${deviceCell(item)}</td>
        <td class="${CELL}">
            <span class="block text-sm">${escapeHtml(item.created_at ?? '')}</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.created_for_humans ?? '')}</span>
        </td>
        <td class="${CELL}">
            <button type="button" data-log-detail="${item.id}" title="Detay"
                class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                <i class="material-symbols-outlined !text-[20px]">visibility</i>
            </button>
        </td>
    </tr>`;
}

/**
 * Satır aksiyonlarındaki "Geçmiş" butonu. Modül index sayfalarının JS'i
 * kendi model sınıfını vererek satır şablonuna ekler:
 *
 *   ${historyButton('App\\Models\\Faq\\Faq', item.id)}
 *
 * Tıklama core/activity-log.js'in kendi delegasyonuyla yakalanır.
 *
 * @param {string} subjectType  Model sınıfının tam adı (subject_type kolonu)
 * @param {number|string} id
 */
export function historyButton(subjectType, id) {
    return `<button type="button" data-activity-history="${escapeHtml(subjectType)}" data-activity-id="${id}"
        data-activity-title="Kayıt Geçmişi" title="Geçmiş"
        class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
        <i class="material-symbols-outlined !text-md">history</i>
    </button>`;
}

/* ------------------------------------------------------------------ *
 * Detay modalı
 * ------------------------------------------------------------------ */

function row(label, value, icon = null) {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    return `<div class="flex items-start gap-[10px] py-[9px] border-b border-gray-100 dark:border-[#172036] last:border-0">
        <span class="w-[150px] shrink-0 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-[5px]">
            ${icon ? `<i class="material-symbols-outlined !text-[15px]">${icon}</i>` : ''}${escapeHtml(label)}
        </span>
        <span class="text-sm text-black dark:text-white break-all min-w-0">${value}</span>
    </div>`;
}

/** Değişiklik tablosu: eski değer üstü çizili kırmızı, yeni değer yeşil. */
function changeTable(changes) {
    const keys = Object.keys(changes ?? {});

    if (keys.length === 0) {
        return '';
    }

    const format = (value) => {
        if (value === null || value === undefined) {
            return '<span class="text-gray-400 italic">boş</span>';
        }

        if (typeof value === 'object') {
            return `<code class="text-xs">${escapeHtml(JSON.stringify(value))}</code>`;
        }

        return escapeHtml(String(value));
    };

    const rows = keys.map((key) => `
        <tr>
            <td class="px-[12px] py-[8px] align-top border-b border-gray-100 dark:border-[#172036] text-xs font-medium whitespace-nowrap">${escapeHtml(key)}</td>
            <td class="px-[12px] py-[8px] align-top border-b border-gray-100 dark:border-[#172036] text-xs text-danger-500 line-through break-all">${format(changes[key].old)}</td>
            <td class="px-[12px] py-[8px] align-top border-b border-gray-100 dark:border-[#172036] text-xs text-success-600 break-all">${format(changes[key].new)}</td>
        </tr>`).join('');

    return `
        <h6 class="!mb-[10px] !text-[13px] mt-[20px]">Değişiklikler</h6>
        <div class="overflow-x-auto rounded-[10px] border border-gray-100 dark:border-[#172036]">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500 dark:text-gray-400">
                        <th class="px-[12px] py-[8px] ltr:text-left rtl:text-right font-medium">Alan</th>
                        <th class="px-[12px] py-[8px] ltr:text-left rtl:text-right font-medium">Eski</th>
                        <th class="px-[12px] py-[8px] ltr:text-left rtl:text-right font-medium">Yeni</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;
}

class LogDetailModal {
    constructor() {
        this.root = null;
    }

    build() {
        const root = document.createElement('div');
        root.id = 'activity-log-detail';
        root.className = 'add-new-popup z-[1405] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = `
            <div class="popup-dialog flex transition-all max-w-[720px] min-h-full items-center mx-auto">
                <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[16px] flex items-center justify-between -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                        <div class="trezo-card-title"><h5 class="!mb-0">Log Detayı</h5></div>
                        <button type="button" data-detail-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                            <i class="ri-close-fill"></i>
                        </button>
                    </div>
                    <div data-detail-body class="max-h-[70vh] overflow-y-auto"></div>
                </div>
            </div>`;
        document.body.append(root);

        this.body = root.querySelector('[data-detail-body]');

        root.addEventListener('click', (event) => {
            if (event.target === root || event.target.closest('[data-detail-close]')) {
                this.close();
            }
        });

        return root;
    }

    async open(id) {
        this.root ??= this.build();
        this.body.innerHTML = '<div class="py-[40px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';
        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        try {
            const { data } = await http.get(`/admin/activity-log/${id}`);
            this.render(data);
        } catch (error) {
            this.body.innerHTML = `<div class="py-[40px] text-center text-danger-500">${escapeHtml(
                error instanceof HttpError ? error.message : 'Kayıt yüklenemedi.',
            )}</div>`;
        }
    }

    close() {
        this.root?.classList.remove('active');

        if (! document.querySelector('.add-new-popup.active')) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    render(item) {
        const roles = (item.causer_roles ?? []).map((role) =>
            `<span class="inline-block py-[2px] px-[7px] rounded-[6px] text-[10px] bg-primary-50 dark:bg-[#15203c] text-primary-500 ltr:mr-[4px] rtl:ml-[4px]">${escapeHtml(role)}</span>`).join('');

        const geoNote = item.geo_status === 'pending'
            ? '<span class="text-xs text-warning-600">Konum çözümlemesi kuyrukta bekliyor (queue:work çalışıyor olmalı).</span>'
            : (item.geo_status === 'skipped'
                ? '<span class="text-xs text-gray-500 dark:text-gray-400">Yerel/özel adres — konum sorgulanmadı.</span>'
                : (item.geo_status === 'failed'
                    ? '<span class="text-xs text-danger-500">Konum çözümlenemedi.</span>'
                    : ''));

        this.body.innerHTML = `
            <div class="flex items-start gap-[12px] mb-[18px]">
                ${eventBadge(item)}
                <p class="!mb-0 text-sm text-black dark:text-white">${escapeHtml(item.description ?? '')}</p>
            </div>

            <h6 class="!mb-[6px] !text-[13px]">Olay</h6>
            ${row('Modül', escapeHtml(item.module_meta?.label ?? item.module), 'category')}
            ${row('Önem', escapeHtml(item.severity_meta?.label ?? item.severity), 'priority_high')}
            ${row('Kayıt', item.subject_label ? escapeHtml(item.subject_label) : null, 'description')}
            ${row('Tarih', escapeHtml(item.created_at ?? ''), 'schedule')}

            <h6 class="!mb-[6px] !text-[13px] mt-[20px]">Kullanıcı</h6>
            ${row('Ad', item.causer_name ? escapeHtml(item.causer_name) : '<span class="text-gray-400">Sistem / oturumsuz</span>', 'person')}
            ${row('E-posta', item.causer_email ? escapeHtml(item.causer_email) : null, 'mail')}
            ${row('Roller', roles || null, 'admin_panel_settings')}

            <h6 class="!mb-[6px] !text-[13px] mt-[20px]">Cihaz ve Tarayıcı</h6>
            ${row('Tarayıcı', item.browser ? `<span class="inline-flex items-center gap-[6px]">${brandIcon(item.browser_icon, item.browser)}${escapeHtml(item.browser)} ${escapeHtml(item.browser_version ?? '')}</span>` : null, 'public')}
            ${row('İşletim sistemi', item.platform ? `<span class="inline-flex items-center gap-[6px]">${brandIcon(item.platform_icon, item.platform)}${escapeHtml(item.platform)} ${escapeHtml(item.platform_version ?? '')}</span>` : null, 'desktop_windows')}
            ${row('Cihaz tipi', item.device_type ? escapeHtml(item.device_type) : null, item.device_icon)}
            ${row('Marka', item.device_brand ? escapeHtml(item.device_brand) : null, 'devices')}
            ${row('Bot', item.is_bot ? 'Evet' : null, 'smart_toy')}
            ${row('User-Agent', item.user_agent ? `<code class="text-[11px]">${escapeHtml(item.user_agent)}</code>` : null, 'code')}

            <h6 class="!mb-[6px] !text-[13px] mt-[20px]">Ağ ve Konum</h6>
            ${row('IP adresi', item.ip_address ? escapeHtml(item.ip_address) : null, 'lan')}
            ${row('Konum', item.location ? escapeHtml(item.location) : geoNote || null, 'location_on')}
            ${row('Bölge', item.region ? escapeHtml(item.region) : null, 'map')}
            ${row('Saat dilimi', item.timezone ? escapeHtml(item.timezone) : null, 'schedule')}
            ${row('Servis sağlayıcı', item.isp ? escapeHtml(item.isp) : null, 'router')}
            ${row('İstek', item.method ? `${escapeHtml(item.method)} ${escapeHtml(item.url ?? '')}` : null, 'http')}
            ${row('Route', item.route_name ? escapeHtml(item.route_name) : null, 'route')}
            ${row('Referer', item.referer ? escapeHtml(item.referer) : null, 'link')}
            ${row('Oturum', item.session_id ? `<code class="text-[11px]">${escapeHtml(item.session_id)}</code>` : null, 'fingerprint')}
            ${row('İstek kimliği', item.request_id ? `<code class="text-[11px]">${escapeHtml(item.request_id)}</code>` : null, 'tag')}
            ${row('Aynı istekteki diğer kayıtlar', item.related_count ? `${item.related_count} kayıt` : null, 'account_tree')}

            ${changeTable(item.changes)}`;
    }
}

const detailModal = new LogDetailModal();

export function openLogDetail(id) {
    return detailModal.open(id);
}

/* ------------------------------------------------------------------ *
 * Liste modalı — modül index sayfaları ve satır bazlı geçmiş
 * ------------------------------------------------------------------ */

class LogListModal {
    constructor() {
        this.root = null;
        this.page = 1;
        this.params = {};
    }

    build() {
        const root = document.createElement('div');
        root.id = 'activity-log-list';
        root.className = 'add-new-popup z-[1404] fixed transition-all inset-0 overflow-x-hidden overflow-y-auto lg:py-[20px]';
        root.innerHTML = `
            <div class="popup-dialog flex transition-all max-w-[1080px] min-h-full items-center mx-auto">
                <div class="trezo-card w-full bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header bg-gray-50 dark:bg-[#15203c] mb-[16px] flex items-center justify-between gap-[12px] -mx-[20px] md:-mx-[25px] -mt-[20px] md:-mt-[25px] p-[20px] md:p-[25px] rounded-t-md">
                        <div class="trezo-card-title"><h5 class="!mb-0" data-list-title>Log Kayıtları</h5></div>
                        <div class="flex items-center gap-[12px]">
                            <a data-list-full href="/admin/activity-log"
                               class="text-sm text-primary-500 transition-all hover:underline whitespace-nowrap">Tümünü gör</a>
                            <button type="button" data-list-close class="text-[23px] transition-all leading-none text-black dark:text-white hover:text-primary-500">
                                <i class="ri-close-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div data-list-body class="max-h-[65vh] overflow-y-auto -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px]"></div>
                    <div data-list-more class="pt-[16px] mt-[16px] border-t border-gray-100 dark:border-[#172036] text-center"></div>
                </div>
            </div>`;
        document.body.append(root);

        this.body = root.querySelector('[data-list-body]');
        this.more = root.querySelector('[data-list-more]');
        this.title = root.querySelector('[data-list-title]');
        this.fullLink = root.querySelector('[data-list-full]');

        root.addEventListener('click', (event) => {
            if (event.target === root || event.target.closest('[data-list-close]')) {
                this.close();

                return;
            }

            const detail = event.target.closest('[data-log-detail]');

            if (detail) {
                openLogDetail(detail.dataset.logDetail);

                return;
            }

            if (event.target.closest('[data-list-load-more]')) {
                this.page++;
                this.load(true);
            }
        });

        return root;
    }

    /**
     * @param {{title?: string, module?: string, subjectType?: string, subjectId?: number|string}} options
     */
    async open(options = {}) {
        this.root ??= this.build();

        this.params = {
            module: options.module ?? '',
            subject_type: options.subjectType ?? '',
            subject_id: options.subjectId ?? '',
            per_page: 20,
        };
        this.page = 1;
        this.showModule = ! options.module;

        this.title.textContent = options.title ?? 'Log Kayıtları';
        this.fullLink.href = options.module
            ? `/admin/activity-log?module=${encodeURIComponent(options.module)}`
            : '/admin/activity-log';

        this.body.innerHTML = '<div class="py-[40px] text-center text-gray-500 dark:text-gray-400">Yükleniyor...</div>';
        this.more.innerHTML = '';
        this.root.classList.add('active');
        document.body.classList.add('overflow-hidden');

        this.load();
    }

    close() {
        this.root?.classList.remove('active');

        if (! document.querySelector('.add-new-popup.active')) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    async load(append = false) {
        try {
            const { data, meta } = await http.get('/admin/activity-log/datatable', { ...this.params, page: this.page });

            if (! data || data.length === 0) {
                if (! append) {
                    this.body.innerHTML = `
                        <div class="py-[50px] text-center">
                            <span class="w-[56px] h-[56px] rounded-full bg-gray-50 dark:bg-[#15203c] inline-flex items-center justify-center mb-[10px]">
                                <i class="material-symbols-outlined !text-[26px] text-gray-400">history</i>
                            </span>
                            <p class="!mb-0 text-gray-500 dark:text-gray-400">Bu kapsamda henüz log kaydı yok.</p>
                        </div>`;
                }

                this.more.innerHTML = '';

                return;
            }

            const rows = data.map((item) => logRow(item, { showModule: this.showModule })).join('');

            if (append) {
                this.body.querySelector('tbody').insertAdjacentHTML('beforeend', rows);
            } else {
                this.body.innerHTML = `
                    <div class="table-responsive overflow-x-auto">
                        <table class="w-full">
                            <tbody class="text-black dark:text-white">${rows}</tbody>
                        </table>
                    </div>`;
            }

            // Sayfalama yerine "daha fazla": modal içinde sayfa numaraları
            // gereksiz karmaşık; kronolojik akışta devam etmek daha doğal.
            this.more.innerHTML = meta && meta.current_page < meta.last_page
                ? `<button type="button" data-list-load-more
                       class="inline-flex items-center gap-[6px] py-[9px] px-[20px] text-sm text-black dark:text-white transition-all rounded-[10px] border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                       <i class="material-symbols-outlined !text-[18px]">expand_more</i> Daha fazla göster
                   </button>`
                : `<span class="text-xs text-gray-500 dark:text-gray-400">Toplam ${meta?.total ?? data.length} kayıt</span>`;
        } catch (error) {
            this.body.innerHTML = `<div class="py-[40px] text-center text-danger-500">${escapeHtml(
                error instanceof HttpError ? error.message : 'Kayıtlar yüklenemedi.',
            )}</div>`;
        }
    }
}

const listModal = new LogListModal();

export function openLogList(options) {
    return listModal.open(options);
}

/**
 * Modül index sayfalarındaki "Log Kayıtları" butonunu ve satırlardaki
 * "Geçmiş" düğmelerini kendiliğinden bağlar. Sayfa JS'inin bir şey
 * çağırmasına gerek yoktur — bileşen bu dosyayı zaten yüklüyor.
 *
 *   <button data-activity-log="blog" data-activity-title="Blog Logları">
 *   <button data-activity-history="App\Models\Blog\Blog" data-activity-id="12">
 */
function bind(root = document) {
    root.addEventListener?.('click', (event) => {
        const listButton = event.target.closest('[data-activity-log]');

        if (listButton) {
            openLogList({
                module: listButton.dataset.activityLog,
                title: listButton.dataset.activityTitle ?? 'Log Kayıtları',
            });

            return;
        }

        const historyButton = event.target.closest('[data-activity-history]');

        if (historyButton) {
            openLogList({
                subjectType: historyButton.dataset.activityHistory,
                subjectId: historyButton.dataset.activityId,
                title: historyButton.dataset.activityTitle ?? 'Kayıt Geçmişi',
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => bind());
