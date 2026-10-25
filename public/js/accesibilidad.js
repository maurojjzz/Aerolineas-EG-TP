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

    function titularImagen(imagen) {
        if (!imagen || imagen.getAttribute("aria-hidden") === "true") return;
        var alt = imagen.getAttribute("alt");
        if (imagen.hasAttribute("title")) return;

        var texto = alt && alt.trim();
        if (!texto) {
            var contenedor = imagen.closest("[aria-label], [title]");
            texto = contenedor?.getAttribute("aria-label") || contenedor?.getAttribute("title");
        }
        if (texto) {
            imagen.setAttribute("title", texto.trim());
        }
    }

    function titularImagenes() {
        document.querySelectorAll("img").forEach(titularImagen);
        var observador = new MutationObserver(function (mutaciones) {
            mutaciones.forEach(function (mutacion) {
                mutacion.addedNodes.forEach(function (nodo) {
                    if (nodo.nodeType !== Node.ELEMENT_NODE) return;
                    if (nodo.tagName === "IMG") titularImagen(nodo);
                    var imagenes = nodo.querySelectorAll("img");
                    imagenes.forEach(titularImagen);
                });
            });
        });
        observador.observe(document.body, { childList: true, subtree: true });
    }

    function iniciar() {
        observarCampos();
        enfocarPrimerError();
        titularImagenes();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", iniciar);
    } else {
        iniciar();
    }
})();
