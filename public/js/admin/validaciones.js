const iNombre = document.getElementById("nombre");
const infoNombre = document.getElementById("infoNombre");

const validarNombre = () => {
    const valor = iNombre.value.trim();

    if (valor === "") {
        iNombre.classList.add("is-invalid");

        infoNombre.textContent = "El nombre es obligatorio.";

        infoNombre.classList.add("text-danger");

        agregarError("nombre", "Nombre de la Aerolinea", "es obligatorio.");

        return false;
    }

    if (valor.length < 3 || valor.length > 50) {
        iNombre.classList.add("is-invalid");

        infoNombre.textContent = "El nombre debe tener entre 3 y 50 caracteres.";

        infoNombre.classList.add("text-danger");

        agregarError("nombre", "Nombre de la Aerolinea", "debe tener entre 3 y 50 caracteres.");

        return false;
    }

    iNombre.classList.remove("is-invalid");
    infoNombre.classList.remove("text-danger");
    infoNombre.textContent = "Nombre con el que operará comercialmente.";

    quitarError("nombre");

    return true;
};

iNombre.addEventListener("blur", validarNombre);

const iCodigo = document.getElementById("codigo");
const infoCodigo = document.getElementById("infoCodigo");

const validarCodigo = () => {
    const valor = iCodigo.value.trim();

    if (valor === "") {
        iCodigo.classList.add("is-invalid");

        infoCodigo.textContent = "El código es obligatorio.";

        infoCodigo.classList.add("text-danger");

        agregarError("codigo", "Código", "es obligatorio.");

        return false;
    }

    if (valor.length < 3 || valor.length > 5) {
        iCodigo.classList.add("is-invalid");

        infoCodigo.textContent = "El código debe tener entre 3 y 5 caracteres.";

        infoCodigo.classList.add("text-danger");

        agregarError("codigo", "Código", "debe tener entre 3 y 5 caracteres.");

        return false;
    }

    iCodigo.classList.remove("is-invalid");
    infoCodigo.classList.remove("text-danger");
    infoCodigo.textContent = "Codigo unico de la aerolínea.";

    quitarError("codigo");

    return true;
};

iCodigo.addEventListener("blur", validarCodigo);

const iPais = document.getElementById("pais");
const infoPais = document.getElementById("infoPais");
const opcionesPais = document.querySelectorAll(".pais-option");

const validarPais = () => {
    const valor = iPais.value.trim().toLowerCase();

    if (valor === "") {
        iPais.classList.add("is-invalid");

        infoPais.textContent = "El país es obligatorio.";

        infoPais.classList.add("text-danger");

        agregarError("pais", "País", "es obligatorio.");

        return false;
    }

    const paisEncontrado = [...opcionesPais].find((option) => {
        const nombre = option.dataset.nombre.toLowerCase();
        const codigo = option.dataset.codigo.toLowerCase();

        return nombre === valor || codigo === valor;
    });

    if (!paisEncontrado) {
        iPais.classList.add("is-invalid");

        infoPais.textContent = "El país ingresado no es válido.";

        infoPais.classList.add("text-danger");

        agregarError("pais", "País", "no es válido.");

        return false;
    }

    iPais.value = paisEncontrado.dataset.nombre;
    iPais.dataset.codigo = paisEncontrado.dataset.codigo;

    iPais.classList.remove("is-invalid");
    infoPais.classList.remove("text-danger");
    infoPais.textContent = "País de origen de la aerolínea.";

    quitarError("pais");

    return true;
};

iPais.addEventListener("blur", validarPais);

const iEmail = document.getElementById("email");
const infoEmail = document.getElementById("infoEmail");

const validarEmail = () => {
    const valor = iEmail.value.trim();

    if (valor === "") {
        iEmail.classList.add("is-invalid");

        infoEmail.textContent = "El email es obligatorio.";

        infoEmail.classList.add("text-danger");

        agregarError("email", "Email de contacto", "es obligatorio.");

        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(valor)) {
        iEmail.classList.add("is-invalid");

        infoEmail.textContent = "El email ingresado no es válido.";

        infoEmail.classList.add("text-danger");

        agregarError("email", "Email de contacto", "no es válido.");

        return false;
    }

    iEmail.classList.remove("is-invalid");
    infoEmail.classList.remove("text-danger");
    infoEmail.textContent = "Email de contacto de la aerolínea.";

    quitarError("email");

    return true;
};

iEmail.addEventListener("blur", validarEmail);

const iLogo = document.getElementById("logo");
const infoLogo = document.getElementById("infoLogo");

const validarLogo = () => {
    const archivo = iLogo.files[0];

    const cuadroLogo = document.getElementById("logoPreview");

    if (!archivo) {
        cuadroLogo.classList.remove("border-danger");
        infoLogo.classList.remove("text-danger");

        infoLogo.textContent = "Formatos: JPG, PNG, WEBP. Max: 2MB";

        return true;
    }

    const tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];
    const tamanoMaximo = 2 * 1024 * 1024;

    if (!tiposPermitidos.includes(archivo.type)) {
        cuadroLogo.classList.add("border-danger");

        infoLogo.textContent = "Solo se permiten imágenes JPG, PNG o WEBP.";

        infoLogo.classList.add("text-danger");

        agregarError("logo", "Logotipo", "solo se permiten imágenes JPG, PNG o WEBP.");

        return false;
    }

    if (archivo.size > tamanoMaximo) {
        cuadroLogo.classList.add("border-danger");

        infoLogo.textContent = "La imagen no puede superar los 2MB.";

        infoLogo.classList.add("text-danger");

        agregarError("logo", "Logotipo", "no puede superar los 2MB.");

        return false;
    }

    cuadroLogo.classList.remove("border-danger");
    infoLogo.classList.remove("text-danger");
    infoLogo.textContent = "Formatos: JPG, PNG, WEBP. Max: 2MB";

    quitarError("logo");

    return true;
};

iLogo.addEventListener("change", validarLogo);

// logica para el cuadro del costado en desktp

const errorBox = document.getElementById("erroresBox");
const recomendacionesBox = document.getElementById("recomendacionesBox");
const cantidadErrores = document.getElementById("cantidadErrores");
const listaErrores = document.getElementById("errorList");

const errores = {};

const agregarError = (campo, titulo, mensaje) => {
    errores[campo] = {
        titulo: titulo,
        mensaje: mensaje,
    };

    actualizarCajaErrores();
};

const quitarError = (campo) => {
    delete errores[campo];

    actualizarCajaErrores();
};

const actualizarCajaErrores = () => {
    const lista = Object.values(errores);

    // no hay errores
    if (lista.length === 0) {
        errorBox.classList.add("d-none");
        recomendacionesBox.classList.remove("d-none");

        return;
    }

    // hay errores
    errorBox.classList.remove("d-none");
    recomendacionesBox.classList.add("d-none");

    // contador
    cantidadErrores.textContent = `${lista.length} ${lista.length === 1 ? "error pendiente" : "errores pendientes"}`;

    // lista
    listaErrores.innerHTML = "";

    lista.forEach((error) => {
        const li = document.createElement("li");

        li.innerHTML = `
            <b>${error.titulo}:</b> ${error.mensaje}
        `;

        listaErrores.appendChild(li);
    });
};
