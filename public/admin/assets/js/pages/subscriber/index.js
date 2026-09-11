/** Bülten abone listesi. */

import { confirm } from '../../core/confirm.js';
import { escapeHtml, http, HttpError } from '../../core/http.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';
import { historyButton } from '../../core/activity-log.js';

const table = new DataTable({
    endpoint: '/admin/subscriber/datatable',
    body: document.getElementById('subscriber-table-body'),
    search: document.getElementById('subscriber-search'),
    filters: {
        status: document.getElementById('subscriber-status'),
        source: document.getElementById('subscriber-source'),
    },
    sort: 'created_at',
    direction: 'desc',
    empty: 'Henüz abone yok.',
    row: (item) => `<tr data-id="${item.id}" class="${item.is_active ? '' : 'opacity-60'}">
        ${cell(`<span class="font-medium">${escapeHtml(item.email)}</span>
            ${item.name ? `<span class="block text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.name)}</span>` : ''}`)}
        ${cell(escapeHtml(item.source_label))}
        ${cell(escapeHtml(item.created_at ?? '—'))}
        ${cell(item.is_active
            ? '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500">Abone</span>'
            : '<span class="inline-block py-[3px] px-[10px] rounded-sm text-xs bg-gray-100 dark:bg-[#15203c] text-gray-600 dark:text-gray-400">Ayrıldı</span>')}
        ${cell(`<div class="flex items-center gap-[9px]">
            ${historyButton('App\\Models\\Subscriber\\Subscriber', item.id)}
            <button type="button" data-delete="${item.id}" title="Sil" class="text-gray-500 dark:text-gray-400 leading-none transition-all hover:text-danger-500">
                <i class="material-symbols-outlined !text-md">delete</i>
            </button>
        </div>`)}
    </tr>`,
});

document.getElementById('subscriber-table-body').addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete]');

    if (! remove) {
        return;
    }

    if (! await confirm('Bu abone kaydı silinsin mi?', { title: 'Aboneyi sil', accept: 'Evet, sil' })) {
        return;
    }

    try {
        const { message } = await http.delete(`/admin/subscriber/${remove.dataset.delete}`);
        toast.success(message);
        table.reload();
    } catch (error) {
        toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
    }
});

table.load();
