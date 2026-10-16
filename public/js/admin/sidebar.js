document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("sidebarToggleHamburguesa");
    const sidebar = document.querySelector(".admin-sidebar");
    const main = document.querySelector(".admin-main");
    const links = document.querySelectorAll(".admin-sidebar .nav-link");
    const spans = document.querySelectorAll(".admin-sidebar span");
    const titleSide = document.querySelector(".custom-logo h6");
    const logo = document.querySelector(".logo-sidebar");

    if (!btn || !sidebar || !main || !titleSide || !logo) {
        console.error("Sidebar: faltan elementos necesarios.");
        return;
    }

    function aplicarEstado(abierto) {
        if (abierto) {
            sidebar.classList.remove("col-md-1", "col-xxl-1");
            sidebar.classList.add("col-md-3", "col-xxl-2");

            main.classList.remove("col-md-11", "col-xxl-11");
            main.classList.add("col-md-9", "col-xxl-10");

            spans.forEach(span => span.classList.remove("d-none"));
            titleSide.classList.remove("d-none");
            logo.style.width = "80px";
            logo.style.height = "58px";
            links.forEach(link => link.classList.remove("justify-content-center"));
        } else {
            sidebar.classList.remove("col-md-3", "col-xxl-2");
            sidebar.classList.add("col-md-1", "col-xxl-1");

            main.classList.remove("col-md-9", "col-xxl-10");
            main.classList.add("col-md-11", "col-xxl-11");

            spans.forEach(span => span.classList.add("d-none"));
            titleSide.classList.add("d-none");
            logo.style.width = "46px";
            logo.style.height = "35px";
            links.forEach(link => link.classList.add("justify-content-center"));
        }
    }

    // 1. Leer el estado guardado al cargar (por defecto cerrado si no hay nada guardado: 'false')
    const sidebarAbierto = localStorage.getItem("sidebar_abierto") === "true";
    aplicarEstado(sidebarAbierto);

    // 2. Alternar y guardar en localStorage al hacer clic en la hamburguesa
    btn.addEventListener("click", function () {
        const actualmenteAbierto = titleSide.classList.contains("d-none") === false;
        const nuevoEstado = !actualmenteAbierto;
        
        aplicarEstado(nuevoEstado);
        localStorage.setItem("sidebar_abierto", nuevoEstado);
    });

});