document.addEventListener("DOMContentLoaded", function () {
    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // Buscador en vivo
    const buscador = document.getElementById("buscadorVuelo");
    const tbody    = document.getElementById("vueloTableBody");
    if (!buscador || !tbody) return;

    buscador.addEventListener("input", function () {
        const texto = this.value.trim().toLowerCase();
        const filas = tbody.querySelectorAll("tr");

        filas.forEach(fila => {
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? "" : "none";
        });
    });
});