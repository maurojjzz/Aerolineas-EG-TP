(function () {
    "use strict";

    // Botón de impresión del reporte activo.
    const btnImprimir = document.getElementById("btnImprimirReporte");
    if (btnImprimir) {
        btnImprimir.addEventListener("click", function () {
            window.print();
        });
    }

    // Enter en el buscador aplica los filtros del formulario contenedor.
    document.querySelectorAll(".js-buscador-reporte").forEach(function (input) {
        input.addEventListener("keydown", function (evento) {
            if (evento.key !== "Enter") return;
            evento.preventDefault();
            const form = input.closest("form");
            if (form) form.submit();
        });
    });

    // Mantiene visible el panel de filtros si la URL trae filtros aplicados.
    const panel = document.getElementById("panelFiltrosReportes");
    if (panel) {
        const params = new URLSearchParams(window.location.search);
        const conFiltros = ["desde", "hasta", "aerolinea", "estadoUsuario", "verificado", "busqueda"]
            .some(function (clave) {
                const valor = params.get(clave);
                return valor !== null && valor !== "";
            });

        if (conFiltros && !panel.classList.contains("show")) {
            panel.classList.add("show");
        }
    }
})();
