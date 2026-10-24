const iTitulo = document.getElementById("titulo");
const infoTitulo = document.getElementById("infoTitulo");
const labTitulo = document.getElementById("labTitulo");

const validarTitulo = () => {
    if (!iTitulo) return true;
    const valor = iTitulo.value.trim();

    if (valor === "") {
        iTitulo.classList.add("is-invalid");
        if (infoTitulo) {
            infoTitulo.textContent = "El título es obligatorio.";
            infoTitulo.classList.add("text-danger");
        }
        if (labTitulo) labTitulo.classList.add("text-danger", "fw-bold");
        return false;
    }

    if (valor.length < 3 || valor.length > 150) {
        iTitulo.classList.add("is-invalid");
        if (infoTitulo) {
            infoTitulo.textContent = "El título debe tener entre 3 y 150 caracteres.";
            infoTitulo.classList.add("text-danger");
        }
        if (labTitulo) labTitulo.classList.add("text-danger", "fw-bold");
        return false;
    }

    iTitulo.classList.remove("is-invalid");
    if (infoTitulo) infoTitulo.classList.remove("text-danger");
    if (labTitulo) labTitulo.classList.remove("text-danger", "fw-bold");
    if (infoTitulo) infoTitulo.textContent = "";

    return true;
};

if (iTitulo) {
    iTitulo.addEventListener("blur", validarTitulo);
}

const iContenido = document.getElementById("contenidoNovedad");
const infoContenido = document.getElementById("infoContenido");
const labContenido = document.getElementById("labContenido");

const validarContenido = () => {
    if (!iContenido) return true;
    const valor = iContenido.value.trim();

    if (valor === "") {
        iContenido.classList.add("is-invalid");
        if (infoContenido) {
            infoContenido.textContent = "El contenido es obligatorio.";
            infoContenido.classList.add("text-danger");
        }
        if (labContenido) labContenido.classList.add("text-danger", "fw-bold");
        return false;
    }

    iContenido.classList.remove("is-invalid");
    if (infoContenido) infoContenido.classList.remove("text-danger");
    if (labContenido) labContenido.classList.remove("text-danger", "fw-bold");
    if (infoContenido) infoContenido.textContent = "";

    return true;
};

if (iContenido) {
    iContenido.addEventListener("blur", validarContenido);
}

const formNovedad = document.getElementById("formNovedad");

if (formNovedad) {
    formNovedad.addEventListener("submit", (e) => {
        const tituloValido = validarTitulo();
        const contenidoValido = validarContenido();

        if (!tituloValido || !contenidoValido) {
            e.preventDefault();
        }
    });
}