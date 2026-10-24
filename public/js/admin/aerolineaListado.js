(function () {
    const tablaBody = document.getElementById("aerolineaTableBody");
    if (!tablaBody) return;

    tablaBody.addEventListener("click", function (e) {
        if (e.target.closest(".btn-toggle-estado")) return;

        const btn = e.target.closest(".btn-accion");
        if (btn) {
            // Los enlaces de acción navegan por su href; no deben abrir el detalle.
            return;
        }

        const fila = e.target.closest(".fila-tabla");
        if (!fila) return;

        const id = fila.dataset.id;
        const filaDetalle = document.getElementById("detalle-" + id);
        if (!filaDetalle) return;

        document.querySelectorAll(".fila-detalle").forEach(function (f) {
            if (f !== filaDetalle) f.classList.add("d-none");
        });

        filaDetalle.classList.toggle("d-none");
    });

})();