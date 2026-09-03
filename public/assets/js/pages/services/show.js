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
    return form.querySelector('[data-error-for="' + name + '"]');
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

  form.addEventListener("submit", function (event) {
    event.preventDefault();

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
    widget.classList.add("is-sent");
    success.hidden = false;
  });
})();
