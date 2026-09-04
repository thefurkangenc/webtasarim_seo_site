import { MediaBrowser } from '../../core/media-browser.js';
import { AjaxModal } from '../../core/modal.js';
import { escapeHtml, http } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const modal = new AjaxModal();
const browser = new MediaBrowser(document.querySelector('[data-media-browser]'), {
    onOpen: (media) => modal.open(`/admin/media/${media.id}/form`, { title: 'Dosya Bilgileri' }),
});

modal.onSubmit(async (form) => {
    const { message } = await http.put(form.action, new FormData(form));

    toast.success(message);
    modal.close();
    browser.load();
});

/*
 * Sidebar — sadece bu sayfada var, picker modalında yok. İşlevi burada:
 * kök klasör kısayolları + "Son Eklenenler"/"Bağlantısız" + depolama özeti.
 * Tıklamalar doğrudan MediaBrowser'ın gezinme API'sini çağırır.
 */
const sidebar = document.querySelector('[data-media-sidebar]');

if (sidebar) {
    const activate = (button) => {
        sidebar.querySelectorAll('[data-sidebar-action], [data-sidebar-folder]').forEach((el) => {
            el.classList.remove('text-primary-500');
            el.classList.add('text-black', 'dark:text-white');
        });
        button.classList.remove('text-black', 'dark:text-white');
        button.classList.add('text-primary-500');
    };

    sidebar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-sidebar-action], [data-sidebar-folder]');

        if (! button) {
            return;
        }

        activate(button);

        if (button.dataset.sidebarAction === 'root') {
            browser.goToRoot();
        } else if (button.dataset.sidebarAction === 'recent') {
            browser.showRecent();
        } else if (button.dataset.sidebarAction === 'unattached') {
            browser.showUnattached();
        } else if (button.dataset.sidebarFolder) {
            browser.goToFolder(Number(button.dataset.sidebarFolder), button.dataset.sidebarFolderName);
        }
    });

    (async () => {
        try {
            const [{ data: folders }, { data: stats }] = await Promise.all([
                http.get('/admin/media/folders'),
                http.get('/admin/media/stats'),
            ]);

            sidebar.querySelector('[data-sidebar-folders]').innerHTML = folders.map((folder) => `
                <li class="font-normal mb-[12px] md:mb-[14px] last:mb-0">
                    <button type="button" data-sidebar-folder="${folder.id}" data-sidebar-folder-name="${escapeHtml(folder.name)}"
                        class="w-full text-left inline-block relative transition-all hover:text-primary-500 ltr:pl-[15px] rtl:pr-[15px] truncate">
                        <span class="ltr:left-0 rtl:right-0 top-1/2 -translate-y-1/2 w-[6px] h-[6px] rounded-full absolute border border-primary-500"></span>
                        ${escapeHtml(folder.name)}
                    </button>
                </li>`).join('');

            sidebar.querySelector('[data-sidebar-stats-text]').textContent = stats.count > 0
                ? `${stats.count} dosya · ${stats.human_size} kullanılıyor`
                : 'Henüz dosya yok.';
        } catch {
            // Sidebar özeti kozmetiktir; başarısız olursa sessizce geç.
        }
    })();
}
