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

    function toggleSidebar() {

        sidebar.classList.toggle("col-xxl-2");
        sidebar.classList.toggle("col-xxl-1");

        main.classList.toggle("col-xxl-10");
        main.classList.toggle("col-xxl-11");

        sidebar.classList.toggle("col-md-3");
        sidebar.classList.toggle("col-md-1");

        main.classList.toggle("col-md-9");
        main.classList.toggle("col-md-11");

        spans.forEach(span => {
            span.classList.toggle("d-none");
        });

        titleSide.classList.toggle("d-none");

        const cerrado = titleSide.classList.contains("d-none");

        logo.style.width = cerrado ? "46px" : "80px";
        logo.style.height = cerrado ? "35px" : "58px";

        links.forEach(link => {
            link.classList.toggle("justify-content-center", cerrado);
        });
    }

    btn.addEventListener("click", toggleSidebar);

    // Auto-colapsar una sola vez al cargar
    setTimeout(() => {
        toggleSidebar();
    }, 1000);
});