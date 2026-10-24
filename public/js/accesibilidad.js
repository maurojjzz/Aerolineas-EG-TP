/**
 * Accesibilidad transversal de Volaré.
 *
 * - Refleja la clase de Bootstrap .is-invalid en el atributo aria-invalid,
 *   para que los lectores de pantalla anuncien los campos con error.
 * - Al enviar un formulario con errores, mueve el foco al primer campo inválido.
 *
 * Se carga con defer desde los headers, así funciona en todas las páginas.
 */
(function () {
    "use strict";

    function sincronizarEstado(campo) {
        if (!campo || !campo.classList) return;

        if (campo.classList.contains("is-invalid")) {
            campo.setAttribute("aria-invalid", "true");
        } else {
            campo.removeAttribute("aria-invalid");
        }
    }

    function observarCampos() {
        var campos = document.querySelectorAll("input, select, textarea");
        if (!campos.length) return;

        var observador = new MutationObserver(function (mutaciones) {
            mutaciones.forEach(function (mutacion) {
                sincronizarEstado(mutacion.target);
            });
        });

        campos.forEach(function (campo) {
            observador.observe(campo, { attributes: true, attributeFilter: ["class"] });
            sincronizarEstado(campo);
        });
    }

    function enfocarPrimerError() {
        document.querySelectorAll("form").forEach(function (form) {
            form.addEventListener("submit", function () {
                window.setTimeout(function () {
                    var invalido = form.querySelector(".is-invalid, [aria-invalid='true']");
                    if (invalido) {
                        invalido.focus();
                    }
                }, 0);
            });
        });
    }

    function iniciar() {
        observarCampos();
        enfocarPrimerError();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", iniciar);
    } else {
        iniciar();
    }
})();
