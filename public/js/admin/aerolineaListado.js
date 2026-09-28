(function () {
    const tablaBody = document.getElementById("aerolineaTableBody");
    if (!tablaBody) return;

    tablaBody.addEventListener("click", function (e) {
        if (e.target.closest(".btn-toggle-estado")) return;

        const btn = e.target.closest(".btn-accion");
        if (btn) {
            const id     = btn.dataset.id;
            const accion = btn.dataset.accion;
            if (accion === "ver")      console.log("ver", id);
            if (accion === "editar")   console.log("editar", id);
            if (accion === "eliminar") console.log("eliminar", id);
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