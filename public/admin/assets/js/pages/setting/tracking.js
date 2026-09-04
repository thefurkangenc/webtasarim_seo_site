/**
 * Site ayarları — izleme kodları. GTM doluysa GA4 doğrudan basılmaz;
 * formda bunu anında gösterir.
 */

const ga4 = document.querySelector('[data-tracking-ga4]');
const gtm = document.querySelector('[data-tracking-gtm]');
const notice = document.querySelector('[data-ga4-skip-notice]');

function syncGa4WithGtm() {
    const skip = Boolean(gtm?.value.trim());

    notice?.classList.toggle('hidden', ! skip);
    ga4?.classList.toggle('opacity-50', skip);
}

gtm?.addEventListener('input', syncGa4WithGtm);
syncGa4WithGtm();
