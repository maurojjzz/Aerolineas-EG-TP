(function () {
    "use strict";

    var lastActionTrigger = null;
    var originalContent = new WeakMap();

    function rememberTrigger(event) {
        if (!(event.target instanceof Element)) {
            return;
        }

        var trigger = event.target.closest(
            ".btn-resolver-ceo, .btn-resolver-promo, .btn-toggle-estado"
        );

        if (trigger) {
            lastActionTrigger = trigger;
        }
    }

    function markTriggerAsLoading() {
        if (!lastActionTrigger || lastActionTrigger.dataset.loading === "1") {
            return;
        }

        lastActionTrigger.dataset.loading = "1";
        lastActionTrigger.classList.add("is-loading");
        lastActionTrigger.setAttribute("aria-busy", "true");

        if (lastActionTrigger.tagName === "BUTTON") {
            lastActionTrigger.disabled = true;
        }
    }

    function setSubmitButtonLoading(button) {
        if (!button || button.dataset.loading === "1") {
            return;
        }

        var label = button.dataset.loadingText || "Procesando…";

        originalContent.set(button, button.innerHTML);
        button.dataset.loading = "1";
        button.disabled = true;
        button.setAttribute("aria-busy", "true");
        button.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
            '<span>' + label + "</span>";
    }

    function restoreLoadingState() {
        document.querySelectorAll('[data-loading="1"]').forEach(function (element) {
            delete element.dataset.loading;
            element.removeAttribute("aria-busy");
            element.classList.remove("is-loading");

            if (element.tagName === "BUTTON") {
                element.disabled = false;
            }

            if (originalContent.has(element)) {
                element.innerHTML = originalContent.get(element);
            }
        });

        document.querySelectorAll("[data-loading-form]").forEach(function (form) {
            delete form.dataset.submitting;
        });

        lastActionTrigger = null;
    }

    document.addEventListener("click", rememberTrigger);

    document.addEventListener("submit", function (event) {
        var form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.hasAttribute("data-loading-form")) {
            return;
        }

        if (form.dataset.submitting === "1") {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = "1";

        var submitButton =
            form.querySelector("[data-loading-button]") ||
            form.querySelector('button[type="submit"]') ||
            form.querySelector("button");

        setSubmitButtonLoading(submitButton);
        markTriggerAsLoading();
    });

    // Si el navegador restaura la página desde caché (botón Atrás),
    // los botones vuelven a su estado normal.
    window.addEventListener("pageshow", restoreLoadingState);
})();
