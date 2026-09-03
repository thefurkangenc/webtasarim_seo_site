import { MediaBrowser } from '../../core/media-browser.js';
import { AjaxModal } from '../../core/modal.js';
import { http } from '../../core/http.js';
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
