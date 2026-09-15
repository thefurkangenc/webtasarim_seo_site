/** Kullanıcı listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError, adminUrl } from '../../core/http.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const MODEL = 'App\\Models\\User';

const avatar = (item) => item.avatar
    ? `<img src="${escapeHtml(item.avatar)}" alt="" class="w-[40px] h-[40px] rounded-full object-cover shrink-0">`
    : `<span class="w-[40px] h-[40px] rounded-full bg-primary-50 dark:bg-[#15203c] text-primary-500 text-xs font-semibold flex items-center justify-center shrink-0">${escapeHtml(item.initials)}</span>`;

const table = new DataTable({
    endpoint: adminUrl('/user/datatable'),
    body: document.getElementById('user-table-body'),
    search: document.getElementById('user-search'),
    filters: {
        role_id: document.getElementById('user-role'),
        is_active: document.getElementById('user-status'),
    },
    sort: 'created_at',
    direction: 'desc',
    empty: 'Henüz kullanıcı eklenmedi.',
    row: (item) => `<tr class="${item.is_self ? 'bg-success-50 dark:bg-[#15203c]' : (item.is_active ? '' : 'opacity-60')}">
        ${cell(`<div class="flex items-center gap-[12px] min-w-0">
            ${avatar(item)}
            <div class="flex items-center gap-[8px] min-w-0">
                <span class="font-medium truncate max-w-[220px]">${escapeHtml(item.name)}</span>
                ${item.is_self ? '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-success-100 dark:bg-[#0c1427] text-success-600 dark:text-success-500 shrink-0">Siz</span>' : ''}
            </div>
        </div>`)}
        ${cell(escapeHtml(item.email))}
        ${cell(item.phone ? escapeHtml(item.phone) : '<span class="text-xs text-gray-400">—</span>')}
        ${cell(`<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-primary-50 dark:bg-[#15203c] text-primary-500">${escapeHtml(item.role)}</span>`)}
        ${cell(item.is_active
            ? '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500">Aktif</span>'
            : '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-danger-100 dark:bg-[#15203c] text-danger-500">Pasif</span>')}
        ${cell(escapeHtml(item.last_login_at ?? '—'))}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton(MODEL, item.id)}
            ${item.can_edit
                ? `<a href="${adminUrl(`/user/${item.id}/edit`)}" title="Düzenle" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined !text-md">edit</i>
                   </a>`
                : ''}
            ${item.can_delete
                ? `<button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                    <i class="material-symbols-outlined !text-md">delete</i>
                   </button>`
                : ''}
        </div>`)}
    </tr>`,
});

document.getElementById('user-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    const confirmed = await confirm('Bu kullanıcı silinsin mi? Panele bir daha giremez.', {
        title: 'Kullanıcıyı sil',
        accept: 'Evet, sil',
    });

    if (! confirmed) {
        return;
    }

    try {
        const { message } = await http.delete(adminUrl(`/user/${remove.dataset.delete}`));
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
