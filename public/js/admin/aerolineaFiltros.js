(function () {
    "use strict";

    var boton = document.getElementById("btnFiltrosAerolinea");
    var panel = document.getElementById("panelFiltrosAerolinea");
    var backdrop = document.getElementById("backdropFiltrosAerolinea");

    if (!boton || !panel || !backdrop) {
        return;
    }

    var escritorio = window.matchMedia("(min-width: 576px)");

    function posicionarPanel() {
        if (!escritorio.matches) {
            panel.style.left = "";
            panel.style.top = "";
            panel.style.bottom = "";
            panel.style.width = "";
            return;
        }

        var rect = boton.getBoundingClientRect();
        var ancho = Math.min(340, window.innerWidth - 16);
        var izquierda = Math.min(rect.right - ancho, window.innerWidth - ancho - 8);

        panel.style.width = ancho + "px";
        panel.style.left = Math.max(8, izquierda) + "px";
        panel.style.top = (rect.bottom + 8) + "px";
        panel.style.bottom = "auto";
    }

    function abrirPanel() {
        panel.classList.remove("d-none");
        backdrop.classList.remove("d-none");
        boton.setAttribute("aria-expanded", "true");
        posicionarPanel();
    }

    function cerrarPanel() {
        panel.classList.add("d-none");
        backdrop.classList.add("d-none");
        boton.setAttribute("aria-expanded", "false");
    }

    boton.addEventListener("click", function () {
        if (panel.classList.contains("d-none")) {
            abrirPanel();
        } else {
            cerrarPanel();
        }
    });

    backdrop.addEventListener("click", cerrarPanel);

    panel.querySelectorAll("[data-cerrar-filtros]").forEach(function (elemento) {
        elemento.addEventListener("click", cerrarPanel);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !panel.classList.contains("d-none")) {
            cerrarPanel();
        }
    });

    window.addEventListener("resize", function () {
        if (!panel.classList.contains("d-none")) {
            posicionarPanel();
        }
    });

    window.addEventListener("scroll", function () {
        if (!panel.classList.contains("d-none") && escritorio.matches) {
            posicionarPanel();
        }
    }, { passive: true });
})();
