/** Rol formu: bölüm/kart tümünü seç, yetki araması, seçili sayısı. */

import { clearErrors, setLoading, showErrors } from '../../core/form.js';
import { http, HttpError, ValidationError, adminUrl } from '../../core/http.js';
import { toast } from '../../core/toast.js';

const form = document.getElementById('role-form');
const search = form.querySelector('[data-permission-search]');
const empty = form.querySelector('[data-permission-empty]');
const selectedCount = form.querySelector('[data-permission-selected]');
const sections = [...form.querySelectorAll('[data-permission-section]')];

const shown = (element) => element && ! element.classList.contains('hidden');

const fold = (element, hide) => {
    element.classList.toggle('hidden', hide);
};

const visibleBoxes = (root) => [...root.querySelectorAll('[data-permission]')].filter((box) => {
    const row = box.closest('[data-permission-row]');
    const group = box.closest('[data-permission-group]');
    const section = box.closest('[data-permission-section]');

    return shown(row) && shown(group) && shown(section);
});

const syncMaster = (master, boxes) => {
    if (! master) {
        return;
    }

    master.checked = boxes.length > 0 && boxes.every((box) => box.checked);
};

const syncGroup = (group) => {
    syncMaster(group.querySelector('[data-select-all]'), visibleBoxes(group));
};

const syncSection = (section) => {
    syncMaster(section.querySelector('[data-select-section]'), visibleBoxes(section));
};

const syncSelected = () => {
    if (selectedCount) {
        selectedCount.textContent = String(form.querySelectorAll('[data-permission]:checked').length);
    }
};

const syncAll = () => {
    form.querySelectorAll('[data-permission-group]').forEach(syncGroup);
    sections.forEach(syncSection);
    syncSelected();
};

const normalize = (value) => value.toLocaleLowerCase('tr').replace(/\s+/g, ' ').trim();

const filterPermissions = () => {
    const query = normalize(search?.value ?? '');

    sections.forEach((section) => {
        const sectionHit = query !== '' && normalize(section.dataset.title ?? '').includes(query);
        let visibleGroups = 0;

        section.querySelectorAll('[data-permission-group]').forEach((group) => {
            const groupHit = sectionHit || (query !== '' && normalize(group.dataset.title ?? '').includes(query));
            let visibleRows = 0;

            group.querySelectorAll('[data-permission-row]').forEach((row) => {
                const haystack = normalize(`${row.dataset.label ?? ''} ${row.dataset.name ?? ''}`);
                const match = query === '' || groupHit || haystack.includes(query);

                fold(row, ! match);

                if (match) {
                    visibleRows += 1;
                }
            });

            fold(group, visibleRows === 0);

            if (visibleRows > 0) {
                visibleGroups += 1;
            }
        });

        fold(section, visibleGroups === 0);
    });

    fold(empty, sections.some(shown));
    syncAll();
};

form.querySelectorAll('[data-permission-group]').forEach((group) => {
    const master = group.querySelector('[data-select-all]');

    master?.addEventListener('change', () => {
        visibleBoxes(group).forEach((box) => {
            box.checked = master.checked;
        });

        syncSection(group.closest('[data-permission-section]'));
        syncSelected();
    });
});

sections.forEach((section) => {
    const master = section.querySelector('[data-select-section]');

    master?.addEventListener('change', () => {
        visibleBoxes(section).forEach((box) => {
            box.checked = master.checked;
        });

        section.querySelectorAll('[data-permission-group]').forEach(syncGroup);
        syncSelected();
    });
});

form.querySelectorAll('[data-permission]').forEach((box) => {
    box.addEventListener('change', () => {
        const group = box.closest('[data-permission-group]');
        const section = box.closest('[data-permission-section]');

        if (group) {
            syncGroup(group);
        }

        if (section) {
            syncSection(section);
        }

        syncSelected();
    });
});

search?.addEventListener('input', filterPermissions);

filterPermissions();

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const id = form.dataset.id;
    const button = form.querySelector('[type=submit]');

    clearErrors(form);
    setLoading(button, true);

    try {
        const { message, data } = id
            ? await http.put(adminUrl(`/role/${id}`), new FormData(form))
            : await http.post(adminUrl('/role'), new FormData(form));

        toast.success(message);

        if (data?.redirect) {
            window.location.href = data.redirect;
        }
    } catch (error) {
        if (error instanceof ValidationError) {
            showErrors(form, error.errors);
            toast.error('Girilen bilgileri kontrol edin.');
        } else {
            toast.error(error instanceof HttpError ? error.message : 'Kaydedilemedi.');
        }
    } finally {
        setLoading(button, false);
    }
});
