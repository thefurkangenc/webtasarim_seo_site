/**
 * Gelen Talepler — liste, filtreler, çoklu seçim + toplu işlem, CSV dışa
 * aktarım ve detay modalı (durum/atama/iç not, e-posta yanıtı, sil/geri al).
 *
 * Seçim sayfa başına çalışır: DataTable her yüklemede satırları yeniden
 * bastığı için sayfa/filtre değişince seçim sıfırlanır — kullanıcı "seçili
 * olduğunu sandığı" bir kaydı yanlışlıkla işleme almasın diye bilinçli.
 */

import { confirm } from '../../core/confirm.js';
import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { escapeHtml, http, HttpError, ValidationError } from '../../core/http.js';
import { AjaxModal } from '../../core/modal.js';
import { cell, DataTable } from '../../core/table.js';
import { toast } from '../../core/toast.js';

const root = document.querySelector('[data-lead-table]');

if (root) {
    const modal = new AjaxModal();
    const selected = new Set();

    const bulkBar = root.querySelector('[data-bulk-bar]');
    const bulkCount = root.querySelector('[data-bulk-count]');
    const selectAll = root.querySelector('[data-select-all]');
    const trashedFilter = document.getElementById('lead-trashed');

    const dateText = (value) => new Date(value).toLocaleString('tr-TR', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });

    const table = new DataTable({
        endpoint: root.dataset.endpoint,
        body: document.getElementById('lead-table-body'),
        perPage: 20,
        sort: 'created_at',
        direction: 'desc',
        empty: 'Henüz talep yok.',
        search: document.getElementById('lead-search'),
        filters: {
            status: document.getElementById('lead-status'),
            assigned_to: document.getElementById('lead-assigned'),
            unread: document.getElementById('lead-unread'),
            trashed: trashedFilter,
            from: document.getElementById('lead-from'),
            to: document.getElementById('lead-to'),
        },
        row: (item) => {
            // Okunmamış satır kalın + sol kenarda renkli şerit.
            const unread = ! item.is_read;

            return `<tr data-id="${item.id}" class="${unread ? 'bg-primary-50/40 dark:bg-[#15203c]/40' : ''}">
                ${cell(`<input type="checkbox" data-row-check value="${item.id}"
                    class="w-[16px] h-[16px] accent-primary-500 cursor-pointer" ${selected.has(item.id) ? 'checked' : ''}>`, 'w-[40px]')}
                ${cell(`
                    <div class="flex items-start gap-[8px]">
                        ${unread ? '<span class="w-[7px] h-[7px] rounded-full bg-primary-500 mt-[6px] shrink-0"></span>' : '<span class="w-[7px] shrink-0"></span>'}
                        <div class="min-w-0">
                            <span class="block truncate max-w-[190px] ${unread ? 'font-semibold' : ''}">${escapeHtml(item.name)}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate max-w-[190px]">${escapeHtml(item.email)}</span>
                            ${item.phone ? `<span class="block text-xs text-gray-400 truncate">${escapeHtml(item.phone)}</span>` : ''}
                        </div>
                    </div>`)}
                ${cell(`
                    <span class="block text-sm truncate max-w-[380px] ${unread ? 'text-black dark:text-white' : 'text-gray-600 dark:text-gray-300'}">${escapeHtml(item.subject || item.preview)}</span>
                    <span class="flex items-center gap-[8px] mt-[3px] text-[11px] text-gray-400">
                        <span>${escapeHtml(item.source_label)}</span>
                        ${item.is_replied ? '<span class="inline-flex items-center gap-[2px] text-success-600"><i class="material-symbols-outlined !text-[13px]">reply</i>yanıtlandı</span>' : ''}
                        ${item.has_note ? '<span class="inline-flex items-center gap-[2px]"><i class="material-symbols-outlined !text-[13px]">sticky_note_2</i>not var</span>' : ''}
                    </span>`)}
                ${cell(`
                    <span class="text-[10px] font-medium py-[1px] px-[8px] text-${item.status_color}-600 bg-${item.status_color}-100 dark:bg-[#ffffff14] inline-block rounded-sm">${escapeHtml(item.status_label)}</span>
                    ${item.assignee ? `<span class="block text-[11px] text-gray-400 mt-[3px] truncate max-w-[120px]">${escapeHtml(item.assignee)}</span>` : ''}`)}
                ${cell(`<span class="text-xs whitespace-nowrap">${dateText(item.created_at)}</span>`)}
                ${cell(`
                    <div class="flex items-center justify-end gap-[6px]">
                        ${item.trashed
                            ? `<button type="button" data-restore="${item.id}" title="Geri al"
                                   class="text-success-600 hover:text-success-500 transition-all"><i class="material-symbols-outlined !text-[20px]">restore_from_trash</i></button>`
                            : `<button type="button" data-open="${item.id}" title="Aç"
                                   class="text-primary-500 hover:text-primary-400 transition-all"><i class="material-symbols-outlined !text-[20px]">open_in_full</i></button>`}
                    </div>`, 'text-right')}
            </tr>`;
        },
    });

    /* ---- çoklu seçim ---- */

    function syncBulkBar() {
        bulkCount.textContent = String(selected.size);
        bulkBar.classList.toggle('hidden', selected.size === 0);
        bulkBar.classList.toggle('flex', selected.size > 0);

        // Çöp kutusu görünümünde "geri al", diğerinde "sil" gösterilir.
        const inTrash = trashedFilter.checked;
        bulkBar.querySelector('[data-bulk="delete"]')?.classList.toggle('hidden', inTrash);
        const restore = bulkBar.querySelector('[data-restore-only]');
        restore?.classList.toggle('hidden', ! inTrash);
        restore?.classList.toggle('inline-flex', inTrash);
    }

    table.body.addEventListener('change', (event) => {
        const box = event.target.closest('[data-row-check]');
        if (! box) return;

        const id = Number(box.value);
        box.checked ? selected.add(id) : selected.delete(id);
        syncBulkBar();
    });

    selectAll.addEventListener('change', () => {
        table.body.querySelectorAll('[data-row-check]').forEach((box) => {
            box.checked = selectAll.checked;
            box.checked ? selected.add(Number(box.value)) : selected.delete(Number(box.value));
        });
        syncBulkBar();
    });

    // Filtre/sayfa değişince seçim anlamını yitirir.
    const originalLoad = table.load.bind(table);
    table.load = async (...args) => {
        selected.clear();
        selectAll.checked = false;
        syncBulkBar();

        return originalLoad(...args);
    };

    async function runBulk(action, status = null) {
        const ids = [...selected];

        if (action === 'delete') {
            const ok = await confirm(`${ids.length} kayıt çöp kutusuna taşınacak. Geri alabilirsiniz.`, {
                title: 'Seçili talepler silinsin mi?',
                accept: 'Sil',
            });

            if (! ok) return;
        }

        try {
            const { message } = await http.post(root.dataset.bulkEndpoint, { ids, action, status });
            toast.success(message);
            await refresh();
        } catch (error) {
            toast.error(error instanceof HttpError ? error.message : 'İşlem yapılamadı.');
        }
    }

    bulkBar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-bulk]');
        if (button) runBulk(button.dataset.bulk);
    });

    bulkBar.querySelector('[data-bulk-status]')?.addEventListener('change', (event) => {
        const status = event.target.value;
        if (! status) return;

        event.target.value = '';
        runBulk('status', status);
    });

    /* ---- özet kartları ---- */

    const statusSelect = document.getElementById('lead-status');
    const unreadBox = document.getElementById('lead-unread');

    document.querySelector('[data-lead-stats]')?.addEventListener('click', (event) => {
        const card = event.target.closest('[data-stat-filter]');
        if (! card) return;

        const status = card.dataset.statFilter;
        const onlyUnread = card.hasAttribute('data-stat-unread');

        unreadBox.checked = onlyUnread;

        // Choices.js native select'in üstüne kendi arayüzünü kuruyor; sadece
        // .value yazmak kutuda görünen etiketi tazelemez (core/select.js
        // örneği bu yüzden select üzerinde saklıyor).
        if (statusSelect.choicesInstance) {
            statusSelect.choicesInstance.setChoiceByValue(status);
        } else {
            statusSelect.value = status;
        }

        statusSelect.dispatchEvent(new Event('change', { bubbles: true }));
    });

    async function refresh() {
        await table.reload();

        try {
            const { data } = await http.get(root.dataset.statsEndpoint);
            data.statuses.forEach((status) => {
                const element = document.querySelector(`[data-stat-count="${status.key}"]`);
                if (element) element.textContent = status.total;
            });
            document.querySelector('[data-stat-today]').textContent = data.today;
            document.querySelector('[data-stat-trashed]').textContent = `(${data.trashed})`;
        } catch {
            // Özet sayıları tazelenemezse liste yine doğru; sessizce geç.
        }
    }

    /* ---- dışa aktarım: mevcut filtrelerle ---- */

    document.getElementById('lead-export')?.addEventListener('click', () => {
        const params = new URLSearchParams();

        Object.entries({
            search: document.getElementById('lead-search').value.trim(),
            status: statusSelect.value,
            assigned_to: document.getElementById('lead-assigned').value,
            unread: unreadBox.checked ? '1' : '',
            trashed: trashedFilter.checked ? '1' : '',
            from: document.getElementById('lead-from').value,
            to: document.getElementById('lead-to').value,
        }).forEach(([key, value]) => value && params.append(key, value));

        window.location.href = `${root.dataset.exportEndpoint}?${params}`;
    });

    /* ---- detay modalı ---- */

    table.body.addEventListener('click', async (event) => {
        const open = event.target.closest('[data-open]');

        if (open) {
            await modal.open(`/admin/lead/${open.dataset.open}`, { title: 'Talep detayı', width: 'max-w-[720px]' });
            bindDetail();
            // Açılınca okundu sayıldığı için liste ve sayılar tazelenir.
            refresh();

            return;
        }

        const restore = event.target.closest('[data-restore]');

        if (restore) {
            try {
                const { message } = await http.post(`/admin/lead/${restore.dataset.restore}/restore`);
                toast.success(message);
                await refresh();
            } catch (error) {
                toast.error(error instanceof HttpError ? error.message : 'Geri alınamadı.');
            }
        }
    });

    function bindDetail() {
        const detail = modal.body.querySelector('[data-lead-detail]');
        if (! detail) return;

        const updateForm = detail.querySelector('[data-lead-update-form]');
        const replyForm = detail.querySelector('[data-lead-reply-form]');

        updateForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = updateForm.querySelector('[type=submit]');
            clearErrors(updateForm);
            setLoading(button, true);

            try {
                const { message } = await http.post(updateForm.action, new FormData(updateForm));
                toast.success(message);
                modal.close();
                await refresh();
            } catch (error) {
                if (error instanceof ValidationError) {
                    showErrors(updateForm, error.errors);
                } else {
                    toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
                }
            } finally {
                setLoading(button, false);
            }
        });

        detail.querySelector('[data-lead-reply-toggle]')?.addEventListener('click', () => {
            replyForm.classList.toggle('hidden');
            replyForm.querySelector('[name=body]')?.focus();
        });

        replyForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = replyForm.querySelector('[type=submit]');
            clearErrors(replyForm);
            setLoading(button, true);

            try {
                const { message } = await http.post(replyForm.action, new FormData(replyForm));
                toast.success(message);
                modal.close();
                await refresh();
            } catch (error) {
                if (error instanceof ValidationError) {
                    showErrors(replyForm, error.errors);
                } else {
                    toast.error(error instanceof HttpError ? error.message : 'Yanıt gönderilemedi.');
                }
            } finally {
                setLoading(button, false);
            }
        });

        detail.querySelector('[data-lead-toggle-read]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            setLoading(button, true);

            try {
                const { message } = await http.post(button.dataset.url);
                toast.success(message);
                modal.close();
                await refresh();
            } catch (error) {
                toast.error(error instanceof HttpError ? error.message : 'İşlem yapılamadı.');
            } finally {
                setLoading(button, false);
            }
        });

        detail.querySelector('[data-lead-delete]')?.addEventListener('click', async (event) => {
            const ok = await confirm('Talep çöp kutusuna taşınacak, gerekirse geri alabilirsiniz.', {
                title: 'Talep silinsin mi?',
                accept: 'Sil',
            });

            if (! ok) return;

            try {
                const { message } = await http.delete(event.currentTarget.dataset.url);
                toast.success(message);
                modal.close();
                await refresh();
            } catch (error) {
                toast.error(error instanceof HttpError ? error.message : 'Silinemedi.');
            }
        });
    }

    trashedFilter.addEventListener('change', syncBulkBar);

    table.load();
}
