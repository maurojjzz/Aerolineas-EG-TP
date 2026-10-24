document.addEventListener("DOMContentLoaded", function () {
    const btn       = document.getElementById("sidebarToggleHamburguesa");
    const sidebar   = document.querySelector(".admin-sidebar");
    const main      = document.querySelector(".admin-main");
    const links     = document.querySelectorAll(".admin-sidebar .nav-link");
    const spans     = document.querySelectorAll(".admin-sidebar span");
    const titleSide = document.querySelector(".custom-logo h6");
    const logo      = document.querySelector(".logo-sidebar");

    if (!btn || !sidebar || !main || !titleSide || !logo) {
        console.warn("Sidebar: faltan elementos en esta vista.");
        return;
    }

    function aplicarEstado(abierto) {
        if (abierto) {
            sidebar.classList.remove("col-md-1", "col-xxl-1");
            sidebar.classList.add("col-md-3", "col-xxl-2");

            main.classList.remove("col-md-11", "col-xxl-11");
            main.classList.add("col-md-9", "col-xxl-10");

            spans.forEach(s => s.classList.remove("d-none"));
            titleSide.classList.remove("d-none");
            logo.style.width  = "80px";
            logo.style.height = "58px";
            links.forEach(l => l.classList.remove("justify-content-center"));
        } else {
            sidebar.classList.remove("col-md-3", "col-xxl-2");
            sidebar.classList.add("col-md-1", "col-xxl-1");

            main.classList.remove("col-md-9", "col-xxl-10");
            main.classList.add("col-md-11", "col-xxl-11");

            spans.forEach(s => s.classList.add("d-none"));
            titleSide.classList.add("d-none");
            logo.style.width  = "46px";
            logo.style.height = "35px";
            links.forEach(l => l.classList.add("justify-content-center"));
        }
    }

    // Leer el estado. Si no hay nada guardado (null), que arranque ABIERTO.
    const guardado = localStorage.getItem("sidebar_abierto");
    const abiertoPorDefecto = guardado === null ? true : guardado === "true";

    // Aplicamos el estado inicial sin animar para evitar el "salto" de layout
    // (el sidebar y el contenido se achicaban/agrandaban al cargar la página).
    sidebar.classList.add("sin-transicion");
    main.classList.add("sin-transicion");
    aplicarEstado(abiertoPorDefecto);
    void sidebar.offsetWidth;
    sidebar.classList.remove("sin-transicion");
    main.classList.remove("sin-transicion");

    // Al hacer clic, cambiar y guardar
    btn.addEventListener("click", function () {
        const actualmenteAbierto = !sidebar.classList.contains("col-md-1");
        const nuevoEstado = !actualmenteAbierto;
        
        aplicarEstado(nuevoEstado);
        localStorage.setItem("sidebar_abierto", nuevoEstado ? "true" : "false");
    });
});