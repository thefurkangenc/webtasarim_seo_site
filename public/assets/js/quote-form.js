/*
 * Teklif formu — sayfadaki HER `[data-quote-widget]` için ayrı çalışır.
 *
 * Eskiden mantık `pages/services/show.js` içindeydi ve forma `#quote-step-form`
 * kimliğiyle bağlanıyordu. Artık form iki yerde birden basılabildiği için
 * (hizmet detayının kenar çubuğu + site genelindeki açılır pencere) kimlik
 * yerine kapsayıcıya bağlanır; alanlar da `name` üzerinden bulunur, böylece
 * iki kopya birbirinin alanını ezmez.
 *
 * Ayrıca sağ alttaki yüzen "Teklif Al" butonunun açtığı pencereyi yönetir.
 */
(function () {
  "use strict";

  var PHONE_RE = /^0 \(\d{3}\) \d{3} \d{2} \d{2}$/;

  /** Ham girdiyi "0 (532) 123 45 67" kalıbına oturtur. */
  function formatTrPhone(raw) {
    var digits = String(raw).replace(/\D/g, "");

    if (digits.indexOf("90") === 0) {
      digits = "0" + digits.slice(2);
    }

    if (digits.indexOf("5") === 0) {
      digits = "0" + digits;
    }

    digits = digits.slice(0, 11);

    if (digits.length === 0) {
      return "";
    }

    var out = digits[0];

    if (digits.length > 1) {
      out += " (" + digits.slice(1, 4);

      if (digits.length >= 4) {
        out += ")";
      }
    }

    if (digits.length > 4) {
      out += " " + digits.slice(4, 7);
    }

    if (digits.length > 7) {
      out += " " + digits.slice(7, 9);
    }

    if (digits.length > 9) {
      out += " " + digits.slice(9, 11);
    }

    return out;
  }

  /**
   * Tema `<select>` elemanlarını jQuery nice-select ile sarıyor. Sarılı bir
   * select ekranda görünmez olur ve kullanıcı seçim yapamaz — teklif formunun
   * kendi stili olduğu için sarmalayıcı sökülür.
   */
  function destroyNiceSelect(select) {
    if (!select || !window.jQuery) {
      return;
    }

    var $select = window.jQuery(select);

    if ($select.next().hasClass("nice-select")) {
      $select.niceSelect("destroy");
    }
  }

  function initWidget(widget) {
    if (widget.dataset.quoteReady === "1") {
      return;
    }

    var form = widget.querySelector(".quote-step-form");

    if (!form) {
      return;
    }

    widget.dataset.quoteReady = "1";

    var track = form.querySelector("[data-quote-track]");
    var viewport = form.querySelector(".quote-steps-viewport");
    var progressFill = form.querySelector("[data-quote-progress]");
    var currentLabel = form.querySelector("[data-quote-current]");
    var backButton = form.querySelector("[data-quote-back]");
    var nextButton = form.querySelector("[data-quote-next]");
    var submitButton = form.querySelector("[data-quote-submit]");
    var formError = form.querySelector("[data-quote-form-error]");
    var success = widget.querySelector("[data-quote-success]");

    var companyInput = form.querySelector('[name="company"]');
    var serviceSelect = form.querySelector('[name="service_id"]');
    var phoneInput = form.querySelector('[name="phone"]');
    var notesInput = form.querySelector('[name="notes"]');

    var steps = Array.prototype.slice.call(form.querySelectorAll(".quote-step"));
    var totalSteps = steps.length;
    var currentStep = 1;

    if (!companyInput || !serviceSelect || !phoneInput) {
      return;
    }

    /* Adımlar yan yana duruyor; görünen yükseklik aktif adıma göre ayarlanır,
       yoksa kısa adımda altta boşluk, uzun adımda taşma olur. */
    function layout() {
      if (!viewport) {
        return;
      }

      widget.style.height = "auto";
      widget.style.minHeight = "";

      steps.forEach(function (step) {
        step.style.minHeight = "0";
        step.style.height = "auto";
      });

      viewport.style.height = "auto";

      var active = steps[currentStep - 1];
      viewport.style.height = (active ? active.scrollHeight : 0) + "px";
    }

    function fieldsForStep(step) {
      if (step === 1) {
        return [companyInput, serviceSelect];
      }

      return notesInput ? [phoneInput, notesInput] : [phoneInput];
    }

    function errorFor(name) {
      // Captcha bileşeni kendi kutusunu data-error ile basar; formun kendi
      // alanları data-error-for kullanıyor.
      return (
        form.querySelector('[data-error-for="' + name + '"]') ||
        form.querySelector('[data-error="' + name + '"]')
      );
    }

    function setInvalid(field, invalid) {
      field.classList.toggle("is-invalid", invalid);

      var error = errorFor(field.getAttribute("name"));

      if (error) {
        error.hidden = !invalid;
      }
    }

    function isFieldValid(field) {
      if (field === companyInput) {
        return field.value.trim() !== "";
      }

      if (field === serviceSelect) {
        return field.value !== "";
      }

      if (field === phoneInput) {
        return PHONE_RE.test(field.value.trim());
      }

      return true;
    }

    function validateStep(step) {
      var fields = fieldsForStep(step);
      var firstInvalid = null;

      fields.forEach(function (field) {
        var ok = isFieldValid(field);
        setInvalid(field, !ok);

        if (!ok && !firstInvalid) {
          firstInvalid = field;
        }
      });

      return firstInvalid || true;
    }

    function isStepValid(step) {
      return fieldsForStep(step).every(isFieldValid);
    }

    function goToStep(step, focus) {
      currentStep = Math.min(Math.max(step, 1), totalSteps);
      track.style.transform = "translateX(-" + (currentStep - 1) * 100 + "%)";
      progressFill.style.width = (currentStep / totalSteps) * 100 + "%";

      if (currentLabel) {
        currentLabel.textContent = String(currentStep);
      }

      widget.dataset.quoteStep = String(currentStep);

      backButton.hidden = currentStep === 1;
      nextButton.hidden = currentStep === totalSteps;
      submitButton.hidden = currentStep !== totalSteps;
      layout();

      if (focus === false) {
        return;
      }

      window.setTimeout(function () {
        var field = fieldsForStep(currentStep)[0];

        if (field && typeof field.focus === "function") {
          field.focus();
        }
      }, 420);
    }

    function next() {
      var result = validateStep(currentStep);

      if (result !== true) {
        layout();
        result.focus();
        return;
      }

      goToStep(currentStep + 1);
    }

    function showFormError(message) {
      if (!formError) {
        return;
      }

      formError.textContent = message || "";
      formError.hidden = !message;
    }

    destroyNiceSelect(serviceSelect);
    layout();
    window.addEventListener("resize", layout);

    phoneInput.addEventListener("input", function () {
      phoneInput.value = formatTrPhone(phoneInput.value);
      setInvalid(phoneInput, false);
    });

    phoneInput.addEventListener("paste", function (event) {
      event.preventDefault();
      var pasted = (event.clipboardData || window.clipboardData).getData("text");
      phoneInput.value = formatTrPhone(pasted);
      setInvalid(phoneInput, false);
    });

    companyInput.addEventListener("input", function () {
      setInvalid(companyInput, false);
    });

    serviceSelect.addEventListener("change", function () {
      setInvalid(serviceSelect, false);
    });

    nextButton.addEventListener("click", next);

    backButton.addEventListener("click", function () {
      goToStep(currentStep - 1);
    });

    form.addEventListener("keydown", function (event) {
      if (event.key !== "Enter" || event.target.tagName === "TEXTAREA") {
        return;
      }

      event.preventDefault();

      if (currentStep < totalSteps) {
        next();
      }
    });

    var fieldStep = { company: 1, service_id: 1, phone: 2, notes: 2, captcha: 2 };

    form.addEventListener("submit", async function (event) {
      event.preventDefault();
      showFormError(null);

      if (!isStepValid(1)) {
        goToStep(1);
        validateStep(1);
        return;
      }

      if (!isStepValid(2)) {
        goToStep(2);
        validateStep(2);
        return;
      }

      submitButton.disabled = true;

      var token = document.querySelector('meta[name="csrf-token"]');

      try {
        var response = await fetch(form.action, {
          method: "POST",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": token ? token.getAttribute("content") : "",
          },
          credentials: "same-origin",
          body: new FormData(form),
        });

        var payload = await response.json().catch(function () {
          return {};
        });

        // Sunucu doğrulaması: hatalı alanın adımına dönülür, mesaj alanın
        // altına basılır.
        if (response.status === 422 && payload.errors) {
          var names = Object.keys(payload.errors);
          var first = names.find(function (name) {
            return fieldStep[name];
          });

          if (first) {
            goToStep(fieldStep[first]);
          }

          names.forEach(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            var error = errorFor(name);

            if (field) {
              field.classList.add("is-invalid");
            }

            if (error) {
              error.textContent = payload.errors[name][0];
              error.hidden = false;
            }
          });

          if (!first) {
            showFormError(payload.message);
          }

          layout();
          return;
        }

        if (!response.ok) {
          showFormError(payload.message || "Talebiniz gönderilemedi. Lütfen tekrar deneyin.");
          layout();
          return;
        }

        widget.classList.add("is-sent");
        success.hidden = false;
      } catch (error) {
        showFormError("Talebiniz gönderilemedi. Lütfen tekrar deneyin.");
        layout();
      } finally {
        if (!widget.classList.contains("is-sent")) {
          submitButton.disabled = false;
        }
      }
    });

    // Pencere açıldığında ilk alana odaklanmak için dışarıdan çağrılır.
    widget.quoteFocus = function () {
      layout();
      var field = fieldsForStep(currentStep)[0];

      if (field && typeof field.focus === "function") {
        field.focus();
      }
    };
  }

  function initAll(root) {
    var scope = root || document;
    Array.prototype.forEach.call(scope.querySelectorAll("[data-quote-widget]"), initWidget);
  }

  /* --- Açılır pencere ------------------------------------------------- */

  function initModal() {
    var modal = document.querySelector("[data-quote-modal]");

    if (!modal) {
      return;
    }

    var lastFocused = null;

    function open() {
      lastFocused = document.activeElement;
      modal.hidden = false;
      // Sınıf bir kare sonra eklenir ki geçiş animasyonu çalışsın.
      window.requestAnimationFrame(function () {
        modal.classList.add("is-open");
      });
      document.body.classList.add("quote-modal-open");

      var widget = modal.querySelector("[data-quote-widget]");

      if (widget && typeof widget.quoteFocus === "function") {
        window.setTimeout(widget.quoteFocus, 220);
      }
    }

    function close() {
      modal.classList.remove("is-open");
      document.body.classList.remove("quote-modal-open");

      window.setTimeout(function () {
        modal.hidden = true;
      }, 220);

      if (lastFocused && typeof lastFocused.focus === "function") {
        lastFocused.focus();
      }
    }

    document.addEventListener("click", function (event) {
      if (event.target.closest("[data-quote-open]")) {
        event.preventDefault();
        open();
        return;
      }

      if (event.target.closest("[data-quote-close]")) {
        event.preventDefault();
        close();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && !modal.hidden) {
        close();
      }
    });
  }

  /* --- Yüzen bloğun girişi ------------------------------------------ */

  function initFloating() {
    var cta = document.querySelector("[data-floating-cta]");

    if (!cta) {
      return;
    }

    // Sayfa açılır açılmaz değil, ziyaretçi biraz kaydırınca girer: ilk
    // ekranı kapatmaz ve giriş hareketi butonun kendi dikkat çekme anı olur.
    var THRESHOLD = 320;
    var shown = false;

    function update() {
      var past = window.pageYOffset > THRESHOLD;

      // Bir kez göründükten sonra gizlenmez — yukarı kaydırmada kaybolan
      // bir eylem butonu ziyaretçiyi aradığını bulamaz duruma sokar.
      if (past && !shown) {
        shown = true;
        cta.classList.add("is-visible");
        window.removeEventListener("scroll", onScroll);
      }
    }

    function onScroll() {
      window.requestAnimationFrame(update);
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    update();

    // Kısa sayfada kaydıracak yer yoksa buton hiç görünmezdi.
    window.setTimeout(function () {
      if (!shown && document.documentElement.scrollHeight <= window.innerHeight + THRESHOLD) {
        shown = true;
        cta.classList.add("is-visible");
        window.removeEventListener("scroll", onScroll);
      }
    }, 1200);
  }

  function boot() {
    initAll();
    initModal();
    initFloating();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
