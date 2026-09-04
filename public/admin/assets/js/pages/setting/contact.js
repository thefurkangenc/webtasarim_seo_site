/**
 * Site ayarları — iletişim formu. Otomatik yanıt ve KVKK alanlarını
 * ilgili anahtara göre gösterir.
 */

const autoReply = document.querySelector('[data-contact-auto-reply]');
const autoReplyFields = document.querySelector('[data-auto-reply-fields]');
const privacy = document.querySelector('[data-contact-privacy]');
const privacyFields = document.querySelector('[data-privacy-fields]');

autoReply?.addEventListener('change', () => {
    autoReplyFields?.classList.toggle('hidden', ! autoReply.checked);
});

privacy?.addEventListener('change', () => {
    privacyFields?.classList.toggle('hidden', ! privacy.checked);
});
