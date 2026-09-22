(function () {
    const btn = document.getElementById("sidebarToggleHamburguesa");
    if (!btn) return; // protección: si no existe el botón, no hace nada

    // Auto-colapsar al cargar
    setTimeout(() => btn.click(), 1000);

    btn.addEventListener("click", function () {
        const sidebar  = document.querySelector(".admin-sidebar");
        const main     = document.querySelector(".admin-main");
        const links    = document.querySelectorAll(".nav-link");
        const spans    = document.querySelectorAll(".admin-sidebar span");
        const titleSide = document.querySelector(".custom-logo h6");
        const logo     = document.querySelector(".logo-sidebar");

        sidebar.classList.toggle("col-xxl-2");
        sidebar.classList.toggle("col-xxl-1");
        main.classList.toggle("col-xxl-10");
        main.classList.toggle("col-xxl-11");
        sidebar.classList.toggle("col-md-3");
        sidebar.classList.toggle("col-md-1");
        main.classList.toggle("col-md-9");
        main.classList.toggle("col-md-11");

        spans.forEach(s => s.classList.toggle("d-none"));
        titleSide.classList.toggle("d-none");

        const cerrado = titleSide.classList.contains("d-none");
        logo.style.width  = cerrado ? "46px" : "80px";
        logo.style.height = cerrado ? "35px" : "58px";

        links.forEach(link => {
            link.classList.toggle("d-flex");
            link.classList.toggle("align-items-center");
            link.classList.toggle("justify-content-center");
        });
    });
})();