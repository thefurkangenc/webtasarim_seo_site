/**
 * Header'daki bildirim merkezi.
 *
 * Öğeler canlı durumdan türetilir (NotificationService): okunmamış talep,
 * kritik sistem kontrolü, kırık link, başarısız kuyruk işi… Sorun çözülünce
 * bildirimi de kendiliğinden kaybolur.
 *
 * Liste SAYFA AÇILIŞINDA bir kez çekilir (rozetin doğru sayıyı göstermesi
 * için şart) ve sonra 60 sn'de bir tazelenir. Açılır menü ayrıca açıldığında
 * tazelenir, böylece kullanıcı menüyü açtığında bayat veri görmez.
 */

import { escapeHtml, http } from './http.js';
import { toast } from './toast.js';

const TONES = {
    primary: 'text-primary-500 bg-primary-50 dark:bg-[#15203c]',
    success: 'text-success-600 bg-success-50 dark:bg-[#15203c]',
    warning: 'text-warning-600 bg-warning-50 dark:bg-[#15203c]',
    danger: 'text-danger-500 bg-danger-50 dark:bg-[#15203c]',
    info: 'text-info-500 bg-info-50 dark:bg-[#15203c]',
};

class NotificationCenter {
    constructor(root) {
        this.root = root;
        this.button = root.querySelector('#dropdownToggleBtn');
        this.badge = root.querySelector('[data-notification-badge]');
        this.count = root.querySelector('[data-notification-count]');
        this.list = root.querySelector('[data-notification-list]');
        this.readAll = root.querySelector('[data-notification-read-all]');

        this.bind();
        this.load();
        setInterval(() => this.load(), 60000);
    }

    bind() {
        // custom.js açılır menüyü `active` sınıfıyla açıyor; sınıf eklendikten
        // SONRA okumak için tıklama kendi dinleyicimizde bir tur geciktirilir.
        this.button?.addEventListener('click', () => {
            setTimeout(() => this.button.classList.contains('active') && this.load(), 0);
        });

        this.readAll?.addEventListener('click', async (event) => {
            event.stopPropagation();

            try {
                const { message, data } = await http.post('/admin/notification/read-all');
                this.render(data);
                toast.success(message);
            } catch {
                toast.error('Bildirimler işaretlenemedi.');
            }
        });

        // Bir bildirime tıklamak onu okundu yapar ve hedefine gider. İstek
        // beklenmez (kullanıcıyı bekletmemek için) ama sekme değişimi de
        // engellenmez — keepalive ile istek sayfa değişse de tamamlanır.
        this.list?.addEventListener('click', (event) => {
            const item = event.target.closest('[data-notification-key]');

            if (! item) {
                return;
            }

            http.post('/admin/notification/read', { key: item.dataset.notificationKey }).catch(() => {});
        });
    }

    async load() {
        try {
            const { data } = await http.get('/admin/notification');
            this.render(data);
        } catch {
            this.list.innerHTML = this.message('Bildirimler alınamadı.');
        }
    }

    render(data) {
        const unread = data.unread ?? 0;

        this.badge.textContent = unread > 9 ? '9+' : String(unread);
        this.badge.hidden = unread === 0;
        this.count.textContent = unread > 0 ? `(${unread})` : '';

        if (! data.items?.length) {
            this.list.innerHTML = this.message('Şu an bildirim yok. Her şey yolunda.');

            return;
        }

        this.list.innerHTML = data.items.map((item) => `
            <a href="${escapeHtml(item.url)}" data-notification-key="${escapeHtml(item.key)}"
                class="flex items-start gap-[11px] px-[20px] py-[13px] border-b border-dashed border-gray-100 dark:border-[#172036] last:border-0 transition-all hover:bg-gray-50 dark:hover:bg-[#15203c] ${item.read ? '' : 'bg-primary-50/40 dark:bg-[#15203c]/40'}">
                <span class="shrink-0 w-[38px] h-[38px] rounded-full flex items-center justify-center ${TONES[item.tone] ?? TONES.info}">
                    <i class="material-symbols-outlined !text-[19px]">${item.icon}</i>
                </span>
                <div class="flex-1 min-w-0">
                    <span class="block text-sm leading-[1.5] ${item.read ? 'text-black dark:text-white' : 'font-semibold text-black dark:text-white'}">
                        ${escapeHtml(item.title)}
                    </span>
                    ${item.body ? `<span class="block text-xs text-gray-500 dark:text-gray-400 truncate">${escapeHtml(item.body)}</span>` : ''}
                    <span class="block text-[11px] text-gray-400 mt-[2px]">${escapeHtml(item.ago ?? '')}</span>
                </div>
                ${item.read ? '' : '<span class="shrink-0 w-[7px] h-[7px] rounded-full bg-primary-500 mt-[6px]" title="Okunmadı"></span>'}
            </a>`).join('');
    }

    message(text) {
        return `<p class="!mb-0 py-[34px] px-[20px] text-center text-sm text-gray-500 dark:text-gray-400">${text}</p>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-notifications]');

    if (root) {
        new NotificationCenter(root);
    }
});
