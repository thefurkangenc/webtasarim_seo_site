/**
 * Sidebar'ı aktif menü öğesine kaydırır.
 *
 * Menü uzun olduğu için alttaki bir sayfaya girildiğinde aktif öğe görünür
 * alanın dışında kalıyordu; kullanıcı her seferinde elle aşağı kaydırmak
 * zorundaydı. Açılışta aktif öğe ortalanır.
 *
 * Kaydırma SimpleBar'ın kendi sarmalayıcısında olur (`data-simplebar`),
 * `.sidebar-area` değil — o `overflow-hidden`.
 */

function scrollToActive() {
    const sidebar = document.getElementById('sidebar-area');

    if (! sidebar) {
        return;
    }

    // Alt menüdeki aktif öğe önceliklidir: üst öğe de "active" sınıfı taşır,
    // ama asıl gidilecek yer alttaki linktir.
    const active = sidebar.querySelector('.sidemenu-link.active')
        ?? sidebar.querySelector('.accordion-button.active');

    if (! active) {
        return;
    }

    const scroller = sidebar.querySelector('.simplebar-content-wrapper')
        ?? sidebar.querySelector('[data-simplebar]');

    if (! scroller || scroller.scrollHeight <= scroller.clientHeight) {
        return;
    }

    // Öğe zaten rahatça görünüyorsa kaydırma — sayfa "zıplamış" gibi olmasın.
    const scrollerBox = scroller.getBoundingClientRect();
    const itemBox = active.getBoundingClientRect();
    const fullyVisible = itemBox.top >= scrollerBox.top + 8
        && itemBox.bottom <= scrollerBox.bottom - 8;

    if (fullyVisible) {
        return;
    }

    const offset = active.offsetTop - (scroller.clientHeight / 2) + (active.offsetHeight / 2);

    scroller.scrollTo({
        top: Math.max(0, offset),
        behavior: 'instant' in document.documentElement.style ? 'instant' : 'auto',
    });
}

// SimpleBar kendi DOM'unu kurduktan sonra çalışmalı; custom.js ile aynı
// karede yarışmamak için bir sonraki boyama adımına bırakılır.
document.addEventListener('DOMContentLoaded', () => {
    requestAnimationFrame(() => requestAnimationFrame(scrollToActive));
});
