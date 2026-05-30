(function () {
  "use strict";

  var formSelector = ".d-powermail form.powermail_form";
  var fieldsetSelector = ".powermail_fieldset";
  var stepSelector = "[data-powermail-morestep-current]";

  function visibleFieldset(form) {
    var fieldsets = Array.from(form.querySelectorAll(fieldsetSelector));
    return fieldsets.find(function (fieldset) {
      return fieldset.offsetParent !== null;
    }) || fieldsets[0] || null;
  }

  function fieldsetIndex(form, fieldset) {
    return Array.from(form.querySelectorAll(fieldsetSelector)).indexOf(fieldset);
  }

  function syncSteps(form) {
    var currentIndex = fieldsetIndex(form, visibleFieldset(form));
    form.querySelectorAll(stepSelector).forEach(function (step, index) {
      var active = index === currentIndex;
      step.classList.toggle("active", active);
      step.dataset.active = active ? "true" : "false";
      step.setAttribute("aria-current", active ? "step" : "false");
    });
  }

  function setCheckboxGroupValidity(group) {
    var boxes = Array.from(group.querySelectorAll('input[type="checkbox"]'));
    var required = group.dataset.desiderioRequired === "true";
    var valid = !required || boxes.some(function (box) {
      return box.checked;
    });

    boxes.forEach(function (box, index) {
      box.setCustomValidity(!valid && index === 0 ? "Please select at least one option." : "");
    });
    group.classList.toggle("d-powermail-invalid", !valid);
    return valid;
  }

  function updateCheckboxGroups(scope) {
    return Array.from(scope.querySelectorAll("[data-desiderio-check-group]")).every(setCheckboxGroupValidity);
  }

  function fieldsInScope(scope) {
    return Array.from(scope.querySelectorAll("input, select, textarea")).filter(function (field) {
      return !field.disabled && field.type !== "hidden";
    });
  }

  function scopeIsValid(scope) {
    updateCheckboxGroups(scope);
    return fieldsInScope(scope).every(function (field) {
      return field.checkValidity();
    });
  }

  function validateScope(scope) {
    updateCheckboxGroups(scope);
    var fields = fieldsInScope(scope);
    var invalid = fields.find(function (field) {
      return !field.checkValidity();
    });

    if (!invalid) {
      return true;
    }

    invalid.reportValidity();
    invalid.focus({ preventScroll: true });
    invalid.scrollIntoView({ block: "center", behavior: "smooth" });
    return false;
  }

  function showFieldset(form, targetFieldset) {
    var fieldsets = Array.from(form.querySelectorAll(fieldsetSelector));
    fieldsets.forEach(function (fieldset) {
      fieldset.style.display = fieldset === targetFieldset ? "block" : "none";
    });
    syncSteps(form);
  }

  function validateBeforeStep(event) {
    var trigger = event.target.closest("[data-powermail-morestep-show]");
    if (!trigger) {
      return;
    }

    var form = trigger.closest("form.powermail_form");
    if (!form || !form.classList.contains("powermail_morestep")) {
      return;
    }

    var currentFieldset = visibleFieldset(form);
    var currentIndex = fieldsetIndex(form, currentFieldset);
    var targetIndex = parseInt(trigger.getAttribute("data-powermail-morestep-show"), 10);

    if (targetIndex > currentIndex && currentFieldset && !validateScope(currentFieldset)) {
      event.preventDefault();
      event.stopImmediatePropagation();
      return;
    }

    window.setTimeout(function () {
      syncSteps(form);
    }, 0);
  }

  function validateBeforeSubmit(event) {
    var submit = event.target.closest('input[type="submit"], button[type="submit"]');
    if (!submit) {
      return;
    }

    var form = submit.closest("form.powermail_form");
    if (!form || !form.classList.contains("powermail_morestep")) {
      return;
    }

    var firstInvalidFieldset = Array.from(form.querySelectorAll(fieldsetSelector)).find(function (fieldset) {
      return !scopeIsValid(fieldset);
    });

    if (firstInvalidFieldset) {
      event.preventDefault();
      event.stopImmediatePropagation();
      showFieldset(form, firstInvalidFieldset);
      validateScope(firstInvalidFieldset);
    }
  }

  function initializeForm(form) {
    form.querySelectorAll("[data-desiderio-check-group]").forEach(function (group) {
      setCheckboxGroupValidity(group);
      group.addEventListener("change", function () {
        setCheckboxGroupValidity(group);
      });
    });
    syncSteps(form);
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(formSelector).forEach(initializeForm);
  });
  document.addEventListener("click", validateBeforeStep, true);
  document.addEventListener("click", validateBeforeSubmit, true);
})();
