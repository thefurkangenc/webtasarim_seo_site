(function () {
  const form = document.getElementById("quote-step-form");

  if (!form) {
    return;
  }

  const widget = document.querySelector("[data-quote-widget]");
  const track = form.querySelector("[data-quote-track]");
  const progressFill = form.querySelector("[data-quote-progress]");
  const currentLabel = form.querySelector("[data-quote-current]");
  const backButton = form.querySelector("[data-quote-back]");
  const nextButton = form.querySelector("[data-quote-next]");
  const submitButton = form.querySelector("[data-quote-submit]");
  const success = document.querySelector("[data-quote-success]");
  const companyInput = form.querySelector("#quote-company");
  const serviceSelect = form.querySelector("#quote-service");
  const phoneInput = form.querySelector("#quote-phone");
  const notesInput = form.querySelector("#quote-notes");
  const steps = Array.from(form.querySelectorAll(".quote-step"));
  const totalSteps = steps.length;

  let currentStep = 1;

  function destroyNiceSelect(select) {
    if (!select || !window.jQuery) {
      return;
    }

    const $select = window.jQuery(select);

    if ($select.next().hasClass("nice-select")) {
      $select.niceSelect("destroy");
    }
  }

  const viewport = form.querySelector(".quote-steps-viewport");

  function layoutQuoteWidget() {
    if (!viewport) {
      return;
    }

    if (widget) {
      widget.style.height = "auto";
      widget.style.minHeight = "";
    }

    steps.forEach(function (step) {
      step.style.minHeight = "0";
      step.style.height = "auto";
    });

    viewport.style.height = "auto";

    const activeStep = steps[currentStep - 1];
    const height = activeStep ? activeStep.scrollHeight : 0;
    viewport.style.height = height + "px";
  }

  function formatTrPhone(raw) {
    let digits = String(raw).replace(/\D/g, "");

    if (digits.startsWith("90")) {
      digits = "0" + digits.slice(2);
    }

    if (digits.startsWith("5")) {
      digits = "0" + digits;
    }

    digits = digits.slice(0, 11);

    if (digits.length === 0) {
      return "";
    }

    let formatted = digits[0];

    if (digits.length > 1) {
      formatted += " (" + digits.slice(1, 4);
      if (digits.length >= 4) {
        formatted += ")";
      }
    }

    if (digits.length > 4) {
      formatted += " " + digits.slice(4, 7);
    }

    if (digits.length > 7) {
      formatted += " " + digits.slice(7, 9);
    }

    if (digits.length > 9) {
      formatted += " " + digits.slice(9, 11);
    }

    return formatted;
  }

  function isValidPhone(value) {
    return /^0 \(\d{3}\) \d{3} \d{2} \d{2}$/.test(value);
  }

  function fieldsForStep(step) {
    if (step === 1) {
      return [companyInput, serviceSelect];
    }

    return [phoneInput, notesInput];
  }

  function errorFor(name) {
    // Captcha bileşeni kendi hata kutusunu data-error ile basar; teklif
    // formunun kendi alanları data-error-for kullanıyor.
    return (
      form.querySelector('[data-error-for="' + name + '"]') ||
      form.querySelector('[data-error="' + name + '"]')
    );
  }

  function setInvalid(field, invalid) {
    field.classList.toggle("is-invalid", invalid);

    const error = errorFor(field.getAttribute("name"));

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
      return isValidPhone(field.value.trim());
    }

    return true;
  }

  function validateStep(step) {
    const fields = fieldsForStep(step);
    let firstInvalid = null;
    let valid = true;

    fields.forEach(function (field) {
      const fieldValid = isFieldValid(field);
      setInvalid(field, !fieldValid);

      if (!fieldValid && !firstInvalid) {
        firstInvalid = field;
        valid = false;
      }
    });

    return firstInvalid ? firstInvalid : (valid ? true : fields[0]);
  }

  function isStepValid(step) {
    return fieldsForStep(step).every(isFieldValid);
  }

  function goToStep(step) {
    currentStep = Math.min(Math.max(step, 1), totalSteps);
    track.style.transform = "translateX(-" + (currentStep - 1) * 100 + "%)";
    progressFill.style.width = (currentStep / totalSteps) * 100 + "%";
    currentLabel.textContent = String(currentStep);
    widget.dataset.quoteStep = String(currentStep);

    backButton.hidden = currentStep === 1;
    nextButton.hidden = currentStep === totalSteps;
    submitButton.hidden = currentStep !== totalSteps;
    layoutQuoteWidget();

    window.setTimeout(function () {
      const field = fieldsForStep(currentStep)[0];

      if (field && typeof field.focus === "function") {
        field.focus();
      }
    }, 420);
  }

  function next() {
    const result = validateStep(currentStep);

    if (result !== true) {
      layoutQuoteWidget();
      result.focus();
      return;
    }

    goToStep(currentStep + 1);
  }

  destroyNiceSelect(serviceSelect);
  layoutQuoteWidget();
  window.addEventListener("resize", layoutQuoteWidget);

  phoneInput.addEventListener("input", function () {
    phoneInput.value = formatTrPhone(phoneInput.value);
    setInvalid(phoneInput, false);
  });

  phoneInput.addEventListener("paste", function (event) {
    event.preventDefault();
    const pasted = (event.clipboardData || window.clipboardData).getData("text");
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
    if (event.key !== "Enter") {
      return;
    }

    if (event.target.tagName === "TEXTAREA") {
      return;
    }

    event.preventDefault();

    if (currentStep < totalSteps) {
      next();
    }
  });

  const formError = form.querySelector("[data-quote-form-error]");
  const token = document.querySelector('meta[name="csrf-token"]');
  const fieldStep = { company: 1, service_id: 1, phone: 2, notes: 2, captcha: 2 };

  function showFormError(message) {
    formError.textContent = message || "";
    formError.hidden = !message;
  }

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

    try {
      const response = await fetch(form.action, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": token ? token.getAttribute("content") : "",
        },
        credentials: "same-origin",
        body: new FormData(form),
      });
      const payload = await response.json().catch(function () {
        return {};
      });

      // Sunucu doğrulaması: hatalı alanın adımına dönülür, mesaj alanın altına basılır.
      if (response.status === 422 && payload.errors) {
        const names = Object.keys(payload.errors);
        const first = names.find(function (name) {
          return fieldStep[name];
        });

        if (first) {
          goToStep(fieldStep[first]);
        }

        names.forEach(function (name) {
          const field = form.querySelector('[name="' + name + '"]');
          const error = errorFor(name);

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

        layoutQuoteWidget();
        return;
      }

      if (!response.ok) {
        showFormError(payload.message || "Talebiniz gönderilemedi. Lütfen tekrar deneyin.");
        layoutQuoteWidget();
        return;
      }

      widget.classList.add("is-sent");
      success.hidden = false;
    } catch (error) {
      showFormError("Talebiniz gönderilemedi. Lütfen tekrar deneyin.");
      layoutQuoteWidget();
    } finally {
      if (!widget.classList.contains("is-sent")) {
        submitButton.disabled = false;
      }
    }
  });
})();

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
