const iNombre = document.getElementById("nombre");
const infoNombre = document.getElementById("infoNombre");

const validarNombre = () => {
    const valor = iNombre.value.trim();
    // Validación para evitar números o símbolos extraños en nombres
    const regexLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;

    if (valor === "") {
        iNombre.classList.add("is-invalid");
        infoNombre.textContent = "El nombre es obligatorio.";
        infoNombre.classList.add("text-danger");
        agregarError("nombre", "Nombre", "es obligatorio.");
        return false;
    }

    if (!regexLetras.test(valor)) {
        iNombre.classList.add("is-invalid");
        infoNombre.textContent = "El nombre solo puede contener letras y espacios.";
        infoNombre.classList.add("text-danger");
        agregarError("nombre", "Nombre", "solo puede contener letras.");
        return false;
    }

    if (valor.length < 2 || valor.length > 50) {
        iNombre.classList.add("is-invalid");
        infoNombre.textContent = "El nombre debe tener entre 2 y 50 caracteres.";
        infoNombre.classList.add("text-danger");
        agregarError("nombre", "Nombre", "debe tener entre 2 y 50 caracteres.");
        return false;
    }

    iNombre.classList.remove("is-invalid");
    infoNombre.classList.remove("text-danger");
    infoNombre.textContent = "Nombre del usuario.";
    quitarError("nombre");
    return true;
};

if (iNombre) iNombre.addEventListener("blur", validarNombre);

const iApellido = document.getElementById("apellido");
const infoApellido = document.getElementById("infoApellido");

const validarApellido = () => {
    const valor = iApellido.value.trim();
    const regexLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;

    if (valor === "") {
        iApellido.classList.add("is-invalid");
        infoApellido.textContent = "El apellido es obligatorio.";
        infoApellido.classList.add("text-danger");
        agregarError("apellido", "Apellido", "es obligatorio.");
        return false;
    }

    if (!regexLetras.test(valor)) {
        iApellido.classList.add("is-invalid");
        infoApellido.textContent = "El apellido solo puede contener letras y espacios.";
        infoApellido.classList.add("text-danger");
        agregarError("apellido", "Apellido", "solo puede contener letras.");
        return false;
    }

    if (valor.length < 2 || valor.length > 50) {
        iApellido.classList.add("is-invalid");
        infoApellido.textContent = "El apellido debe tener entre 2 y 50 caracteres.";
        infoApellido.classList.add("text-danger");
        agregarError("apellido", "Apellido", "debe tener entre 2 y 50 caracteres.");
        return false;
    }

    iApellido.classList.remove("is-invalid");
    infoApellido.classList.remove("text-danger");
    infoApellido.textContent = "Apellido del usuario.";
    quitarError("apellido");
    return true;
};

if (iApellido) iApellido.addEventListener("blur", validarApellido);

const iNroDocumento = document.getElementById("nroDocumento");
const infoNroDoc = document.getElementById("infoNroDoc");

const validarDocumento = () => {
    const valor = iNroDocumento.value.trim();

    if (valor === "") {
        iNroDocumento.classList.add("is-invalid");
        infoNroDoc.textContent = "El número de documento es obligatorio.";
        infoNroDoc.classList.add("text-danger");
        agregarError("nroDocumento", "Nro de Documento", "es obligatorio.");
        return false;
    }

    if (valor.length < 6 || valor.length > 15) {
        iNroDocumento.classList.add("is-invalid");
        infoNroDoc.textContent = "El documento debe tener entre 6 y 15 caracteres.";
        infoNroDoc.classList.add("text-danger");
        agregarError("nroDocumento", "Nro de Documento", "debe tener entre 6 y 15 caracteres.");
        return false;
    }

    iNroDocumento.classList.remove("is-invalid");
    infoNroDoc.classList.remove("text-danger");
    infoNroDoc.textContent = "Documento único en la plataforma.";
    quitarError("nroDocumento");
    return true;
};

if (iNroDocumento) iNroDocumento.addEventListener("blur", validarDocumento);

const iEmail = document.getElementById("email");
const infoEmail = document.getElementById("infoEmail");

const validarEmail = () => {
    const valor = iEmail.value.trim();

    if (valor === "") {
        iEmail.classList.add("is-invalid");
        infoEmail.textContent = "El email es obligatorio.";
        infoEmail.classList.add("text-danger");
        agregarError("email", "Email", "es obligatorio.");
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(valor)) {
        iEmail.classList.add("is-invalid");
        infoEmail.textContent = "El email ingresado no es válido.";
        infoEmail.classList.add("text-danger");
        agregarError("email", "Email", "no es válido.");
        return false;
    }

    iEmail.classList.remove("is-invalid");
    infoEmail.classList.remove("text-danger");
    infoEmail.textContent = "Correo institucional o personal.";
    quitarError("email");
    return true;
};

if (iEmail) iEmail.addEventListener("blur", validarEmail);

// Lógica de la caja lateral de errores (Idéntica a aerolíneas)
const errorBox = document.getElementById("erroresBox");
const recomendacionesBox = document.getElementById("recomendacionesBox");
const cantidadErrores = document.getElementById("cantidadErrores");
const listaErrores = document.getElementById("errorList");

const errores = {};

const agregarError = (campo, titulo, mensaje) => {
    errores[campo] = { titulo: titulo, mensaje: mensaje };
    actualizarCajaErrores();
};

const quitarError = (campo) => {
    delete errores[campo];
    actualizarCajaErrores();
};

const actualizarCajaErrores = () => {
    if (!errorBox || !recomendacionesBox) return;
    const lista = Object.values(errores);

    if (lista.length === 0) {
        errorBox.classList.add("d-none");
        recomendacionesBox.classList.remove("d-none");
        return;
    }

    errorBox.classList.remove("d-none");
    recomendacionesBox.classList.add("d-none");

    cantidadErrores.textContent = `${lista.length} ${lista.length === 1 ? "error pendiente" : "errores pendientes"}`;
    listaErrores.innerHTML = "";

    lista.forEach((error) => {
        const li = document.createElement("li");
        li.innerHTML = `<b>${error.titulo}:</b> ${error.mensaje}`;
        listaErrores.appendChild(li);
    });
};

// Validación en el envío del formulario (Submit)
const formEditarUsuario = document.querySelector("form[action*='accion=editar']");

if (formEditarUsuario) {
    formEditarUsuario.addEventListener("submit", (e) => {
        const nombreValido = validarNombre();
        const apellidoValido = validarApellido();
        const documentoValido = validarDocumento();
        const emailValido = validarEmail();

        if (!nombreValido || !apellidoValido || !documentoValido || !emailValido) {
            e.preventDefault();
        }
    });
}