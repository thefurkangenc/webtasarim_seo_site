import { MediaBrowser } from '../../core/media-browser.js';
import { AjaxModal } from '../../core/modal.js';
import { escapeHtml, http } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const modal = new AjaxModal();

/*
 * Sidebar — sadece bu sayfada var, picker modalında yok. Hızlı erişim
 * kısayolları + açılıp kapanan klasör ağacı + depolama özeti.
 *
 * Aktif satır MediaBrowser'ın onNavigate geri çağrısıyla işaretlenir; böylece
 * yalnızca sidebar tıklamasında değil, breadcrumb'dan veya bir klasöre çift
 * tıklayarak gezinildiğinde de doğru satır vurgulanır.
 */
const sidebar = document.querySelector('[data-media-sidebar]');

/** Klasör kimliği -> kökten o klasöre kadar [{id, name}] zinciri. */
const folderPaths = new Map();

const ACTIVE = ['bg-primary-50', 'dark:bg-primary-500/10', 'text-primary-500'];
const IDLE = ['text-black', 'dark:text-white'];

function setActive(row) {
    if (! sidebar) {
        return;
    }

    sidebar.querySelectorAll('[data-sidebar-action], [data-sidebar-row]').forEach((element) => {
        element.classList.remove(...ACTIVE);
        element.classList.add(...IDLE);
    });

    if (row) {
        row.classList.remove(...IDLE);
        row.classList.add(...ACTIVE);
    }
}

/** Aktif klasörün görünür olması için tüm ata dallarını açar. */
function revealFolder(row) {
    let branch = row?.parentElement?.closest('[data-folder-children]');

    while (branch) {
        branch.classList.remove('hidden');
        branch.previousElementSibling
            ?.querySelector('[data-folder-toggle] i')
            ?.classList.add('rotate-90');
        branch = branch.parentElement?.closest('[data-folder-children]');
    }
}

function folderTree(folders, path = [], depth = 0) {
    return folders.map((folder) => {
        const trail = [...path, { id: folder.id, name: folder.name }];
        folderPaths.set(folder.id, trail);

        const children = folder.children ?? [];
        const indent = 4 + depth * 14;

        return `
            <li>
                <div data-sidebar-row data-folder-id="${folder.id}"
                    class="flex items-center rounded-[10px] transition-all text-black dark:text-white hover:bg-gray-50 dark:hover:bg-[#15203c]"
                    style="padding-inline-start: ${indent}px">
                    ${children.length
                        ? `<button type="button" data-folder-toggle
                               class="w-[20px] h-[30px] shrink-0 inline-flex items-center justify-center text-gray-400 hover:text-primary-500 transition-all">
                               <i class="ri-arrow-right-s-line text-[16px] transition-transform"></i>
                           </button>`
                        : '<span class="w-[20px] shrink-0"></span>'}
                    <button type="button" data-sidebar-folder="${folder.id}"
                        class="flex items-center gap-[8px] grow min-w-0 text-left py-[7px] ltr:pr-[10px] rtl:pl-[10px] text-sm transition-all">
                        <i class="ri-folder-fill text-[16px] text-[#f2b544] shrink-0 leading-none"></i>
                        <span class="truncate" title="${escapeHtml(folder.name)}">${escapeHtml(folder.name)}</span>
                    </button>
                </div>
                ${children.length
                    ? `<ul data-folder-children class="hidden flex flex-col gap-[2px] mt-[2px]">
                           ${folderTree(children, trail, depth + 1)}
                       </ul>`
                    : ''}
            </li>`;
    }).join('');
}

const browser = new MediaBrowser(document.querySelector('[data-media-browser]'), {
    onOpen: (media) => modal.open(`/admin/media/${media.id}/form`, { title: 'Dosya Bilgileri' }),
    onNavigate: ({ mode, folderId }) => {
        if (! sidebar) {
            return;
        }

        if (mode !== 'folder') {
            setActive(sidebar.querySelector(`[data-sidebar-action="${mode}"]`));

            return;
        }

        if (folderId === null) {
            setActive(sidebar.querySelector('[data-sidebar-action="root"]'));

            return;
        }

        const row = sidebar.querySelector(`[data-sidebar-row][data-folder-id="${folderId}"]`);
        revealFolder(row);
        setActive(row);
    },
});

modal.onSubmit(async (form) => {
    const { message } = await http.put(form.action, new FormData(form));

    toast.success(message);
    modal.close();
    browser.load();
});

if (sidebar) {
    sidebar.addEventListener('click', (event) => {
        // Ok butonu yalnızca dalı açıp kapatır, gezinmeyi tetiklemez.
        const toggle = event.target.closest('[data-folder-toggle]');

        if (toggle) {
            const branch = toggle.closest('[data-sidebar-row]').nextElementSibling;
            branch?.classList.toggle('hidden');
            toggle.querySelector('i')?.classList.toggle('rotate-90', ! branch?.classList.contains('hidden'));

            return;
        }

        const button = event.target.closest('[data-sidebar-action], [data-sidebar-folder]');

        if (! button) {
            return;
        }

        if (button.dataset.sidebarAction === 'root') {
            browser.goToRoot();
        } else if (button.dataset.sidebarAction === 'recent') {
            browser.showRecent();
        } else if (button.dataset.sidebarAction === 'unattached') {
            browser.showUnattached();
        } else if (button.dataset.sidebarFolder) {
            // Tam yol verilir — aksi halde iç içe bir klasöre atlarken
            // breadcrumb kökün hemen altındaymış gibi görünürdü.
            browser.goToPath(folderPaths.get(Number(button.dataset.sidebarFolder)) ?? []);
        }
    });

    (async () => {
        try {
            const [{ data: tree }, { data: stats }] = await Promise.all([
                http.get('/admin/media/folders/tree'),
                http.get('/admin/media/stats'),
            ]);

            const list = sidebar.querySelector('[data-sidebar-folders]');

            list.innerHTML = tree.length
                ? folderTree(tree)
                : '<li class="text-sm text-gray-500 dark:text-gray-400 px-[12px] py-[6px]">Henüz klasör yok.</li>';

            // Ağaç sonradan geldi; o an bulunulan klasör varsa şimdi işaretle.
            browser.options.onNavigate?.({ mode: browser.mode, folderId: browser.currentFolderId });

            const bar = sidebar.querySelector('[data-sidebar-stats-bar]');
            // Birkaç yüz KB'lik kullanım 5 GB kotada %0 çıkıyor; çubuk tamamen
            // kaybolmasın diye dolu olduğunda en az bir iz bırakılır.
            bar.style.width = stats.count > 0 ? `${Math.max(stats.percent, 1.5)}%` : '0%';

            sidebar.querySelector('[data-sidebar-stats-text]').textContent = stats.count > 0
                ? `${stats.human_size} / ${stats.quota_human} · ${stats.count} dosya`
                : 'Henüz dosya yok.';
        } catch {
            // Sidebar özeti kozmetiktir; başarısız olursa sessizce geç.
        }
    })();
}
