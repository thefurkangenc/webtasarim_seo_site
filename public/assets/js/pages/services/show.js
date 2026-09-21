/*
 * Hizmet detay sayfasi.
 * Teklif formunun mantigi artik ortak: public/assets/js/quote-form.js
 * (bilesen kendisi yukler). Burada yalnizca bolge arama kutusu kaldi.
 */

/**
 * Bölge listesi araması. Sunucu tüm bölgeleri basar (linkler HTML'de kalsın
 * diye); buradaki filtre yalnızca görünürlüğü kısar. Bir ilin kendisi
 * eşleşmiyor ama altındaki bir ilçe eşleşiyorsa il de görünür kalır, yoksa
 * sonuç bağlamsız bir ilçe listesi olur.
 */
(function () {
  const widget = document.querySelector("[data-region-widget]");
  const input = widget && widget.querySelector("[data-region-search]");

  if (!input) {
    return;
  }

  const groups = Array.from(widget.querySelectorAll("[data-region-group]"));
  const empty = widget.querySelector("[data-region-empty]");

  function normalize(value) {
    return value
      .toLocaleLowerCase("tr")
      .normalize("NFD")
      .replace(/[̀-ͯ]/g, "");
  }

  input.addEventListener("input", function () {
    const term = normalize(input.value.trim());
    let visible = 0;

    groups.forEach(function (group) {
      const city = group.querySelector(".region-city");
      const children = Array.from(group.querySelectorAll(".region-children li"));
      const cityMatches = normalize(city.textContent).includes(term);
      let shown = 0;

      children.forEach(function (item) {
        const match = cityMatches || normalize(item.textContent).includes(term);
        item.hidden = !match;

        if (match) {
          shown += 1;
        }
      });

      const groupMatches = cityMatches || shown > 0;
      group.hidden = !groupMatches;

      // Gruplar arası ayraç komşuluğa bağlı; gizlenen gruplar DOM'da kaldığı
      // için ilk görünen grubun tepesinde boşta bir çizgi kalırdı.
      group.classList.toggle("is-first", groupMatches && visible === 0);

      if (groupMatches) {
        visible += 1;
      }
    });

    if (empty) {
      empty.hidden = visible > 0;
    }
  });
})();
