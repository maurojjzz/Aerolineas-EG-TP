// importo lo neceasrio para tooltip

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));

function iniciarTooltips(contenedor = document) {
    const tooltips = contenedor.querySelectorAll('[data-bs-toggle="tooltip"]');

    tooltips.forEach((elemento) => {
        bootstrap.Tooltip.getOrCreateInstance(elemento);
    });
}


// opciones del select pais

const paisInput = document.getElementById("pais");
const paisCodigo = document.getElementById("paisCodigo");
const paisResults = document.getElementById("paises-results");
const paisOptions = document.querySelectorAll(".pais-option");

paisInput.addEventListener("focus", function () {
    paisResults.style.display = "block";
});

// Filtrar mientras escribe.
paisInput.addEventListener("input", function () {
    const texto = paisInput.value.trim().toLowerCase();

    paisCodigo.value = "";
    paisInput.dataset.codigo = "";
    paisResults.style.display = "block";

    paisOptions.forEach((option) => {
        const nombre = option.dataset.nombre.toLowerCase();
        const codigo = option.dataset.codigo.toLowerCase();

        option.style.display =
            nombre.includes(texto) || codigo.includes(texto)
                ? "flex"
                : "none";
    });
});

// mousedown evita que el blur impida seleccionar la opción.
paisOptions.forEach((option) => {
    option.addEventListener("mousedown", function (event) {
        event.preventDefault();

        paisInput.value = this.dataset.nombre;
        paisInput.dataset.codigo = this.dataset.codigo;
        paisCodigo.value = this.dataset.codigo;
        paisResults.style.display = "none";
    });
});

paisInput.addEventListener("blur", function () {
    setTimeout(() => {
        const texto = paisInput.value.trim().toLowerCase();
        const paisEncontrado = [...paisOptions].find((option) => {
            return (
                option.dataset.nombre.toLowerCase() === texto ||
                option.dataset.codigo.toLowerCase() === texto
            );
        });

        if (paisEncontrado) {
            paisInput.value = paisEncontrado.dataset.nombre;
            paisInput.dataset.codigo = paisEncontrado.dataset.codigo;
            paisCodigo.value = paisEncontrado.dataset.codigo;
        } else {
            paisCodigo.value = "";
            paisInput.dataset.codigo = "";
        }

        paisResults.style.display = "none";
    }, 150);
});

document.addEventListener("click", function (event) {
    if (!paisInput.contains(event.target) && !paisResults.contains(event.target)) {
        paisResults.style.display = "none";
    }
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

setTimeout(() => {
    menHambAdm.click();
}, 1000);

// manejo de la foto


const logoInput = document.getElementById("logo");
const cuadroFoto = document.getElementById("logoPreview");

const cuadroOg = cuadroFoto.innerHTML;

logoInput.addEventListener("change", function () {
    const archivo = logoInput.files[0];

    if (!archivo) return;

    const validTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!validTypes.includes(archivo.type)) {
        alert("Solo se permiten imágenes JPG, PNG o WEBP.");
        logoInput.value = "";
        return;
    }

    if (archivo.size > 2 * 1024 * 1024) {
        alert("La imagen no puede superar los 2MB.");
        logoInput.value = "";
        return;
    }


    const urlImagen = URL.createObjectURL(archivo);

    cuadroFoto.style.border = "2px solid #d3d7dd";

    cuadroFoto.innerHTML = `

        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;

    setTimeout(() => {
        cuadroFoto.classList.replace("justify-content-center", "justify-content-start");

        cuadroFoto.innerHTML = `
            <img
                src="${urlImagen}"
                class="logo-preview-img img-fluid"
                alt="preview logo"
            >

            <div class="d-flex flex-column w-100">
                <p class="m-0 fw-semibold text-break">
                    ${archivo.name}
                </p>

                <p class="m-0 form-text-info">
                    ${(archivo.size / 1024 / 1024).toFixed(2)} MB
                </p>

                <div class=" d-flex justify-content-end align-items-center gap-1 p-0">
                    <button
                        type="button"
                        id="cambiarLogo"
                        class="p-0 text-start bg-transparent border-0"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        data-bs-title="Cambiar logotipo"
                        data-bs-trigger="hover"
                    >
                        <img
                            src="../../../public/img/icons/redo.png"
                            class="img-fluid btn-mini"
                            alt="icono cambiar foto logo"
                        >
                    </button>

                    <button 
                        type="button"
                        id="quitarLogo"
                        class=" p-0 text-start bg-transparent border-0"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        data-bs-title="Eliminar logotipo"
                        data-bs-trigger="hover"
                    >
                        <img
                            src="../../../public/img/icons/trash.png"
                            class= "img-fluid btn-mini"
                            alt="icono quitar foto logo"
                        >
                    </button>
                </div>
        </div>
        `;
        iniciarTooltips();

        document.getElementById("cambiarLogo").addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const tooltip = bootstrap.Tooltip.getInstance(this);

            if (tooltip) {
                tooltip.hide();
            }

            this.blur();
            logoInput.click();
        });

        document.getElementById("quitarLogo").addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const tooltip = bootstrap.Tooltip.getInstance(this);

            if (tooltip) {
                tooltip.hide();
            }

            this.blur();

            logoInput.value = "";

            cuadroFoto.innerHTML = cuadroOg;

            cuadroFoto.classList.remove("justify-content-start");
            cuadroFoto.classList.add("justify-content-center");

            cuadroFoto.style.border = " 2px dashed #d3d7dd";
            

        });

    }, 1500);
});
