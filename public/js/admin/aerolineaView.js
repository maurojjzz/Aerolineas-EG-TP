const paisInput = document.getElementById("pais");
const paisResults = document.getElementById("paises-results");
const paisOptions = document.querySelectorAll(".pais-option");

paisInput.addEventListener("focus", function () {
    paisResults.style.display = "block";
});

paisOptions.forEach((option) => {
    option.addEventListener("click", function () {
        paisInput.value = this.dataset.nombre;

        paisResults.style.display = "none";
    });
});

/*sidebar menu hamburguesa*/

const menHambAdm = document.getElementById("sidebarToggleHamburguesa");

menHambAdm.addEventListener("click", function () {
    const sidebar = document.querySelector(".admin-sidebar");
    const main = document.querySelector(".admin-main");
    const link = document.querySelectorAll(".nav-link");
    const spanSidebar = document.querySelectorAll(".admin-sidebar span");
    const titleSide = document.querySelector(".custom-logo h6");
    const logo = document.querySelector(".logo-sidebar");

    sidebar.classList.toggle("col-xxl-2");
    sidebar.classList.toggle("col-xxl-1");

    main.classList.toggle("col-xxl-10");
    main.classList.toggle("col-xxl-11");
    sidebar.classList.toggle("col-md-3");
    sidebar.classList.toggle("col-md-1");

    main.classList.toggle("col-md-9");
    main.classList.toggle("col-md-11");

    spanSidebar.forEach((span) => {
        span.classList.toggle("d-none");
    });

    titleSide.classList.toggle("d-none");
    const sidebarCerrado = titleSide.classList.contains("d-none");

    logo.style.width = sidebarCerrado ? "46px" : "80px";
    logo.style.height = sidebarCerrado ? "35px" : "58px";

    link.forEach((icono) => {
        icono.classList.toggle("d-flex");
        icono.classList.toggle("align-items-center");
        icono.classList.toggle("justify-content-center");
    });
});
