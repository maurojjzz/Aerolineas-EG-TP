const iNombre = document.getElementById("nombre");
const infoNombre = document.getElementById("infoNombre");

const validarNombre = () => {
    const valor = iNombre.value.trim();

    if (valor === "") {
        iNombre.classList.add("is-invalid");

        infoNombre.textContent = "El nombre es obligatorio.";

        infoNombre.classList.add("text-danger");

        document.getElementById("labNombre").classList.add("text-danger", "fw-bold"); 

        return false;
    }

    if (valor.length < 3 || valor.length > 50) {
        iNombre.classList.add("is-invalid");

        infoNombre.textContent = "El nombre debe tener entre 3 y 50 caracteres.";

        infoNombre.classList.add("text-danger");

        return false;
    }

    iNombre.classList.remove("is-invalid");
    infoNombre.classList.remove("text-danger");
    document.getElementById("labNombre").classList.remove("text-danger", "fw-bold"); 
    infoNombre.textContent = "";

    return true;
};

iNombre.addEventListener("blur", validarNombre);

const iApellido = document.getElementById("apellido");
const infoApellido = document.getElementById("infoApellido");

const validarApellido = () => {
    const valor = iApellido.value.trim();

    if (valor === "") {
        iApellido.classList.add("is-invalid");
        infoApellido.textContent = "El apellido es obligatorio.";
        infoApellido.classList.add("text-danger");
        document.getElementById("labApellido").classList.add("text-danger", "fw-bold");

        return false;
    }

    if (valor.length < 2 || valor.length > 50) {
        iApellido.classList.add("is-invalid");
        infoApellido.textContent = "El apellido debe tener entre 2 y 50 caracteres.";
        infoApellido.classList.add("text-danger");

        return false;
    }

    iApellido.classList.remove("is-invalid");
    infoApellido.classList.remove("text-danger");
    document.getElementById("labApellido").classList.remove("text-danger", "fw-bold");
    infoApellido.textContent = "";

    return true;
};

iApellido.addEventListener("blur", validarApellido);

const iNumeroDocumento = document.getElementById("numero_documento");
const tipoDocumento = document.getElementById("tipo_documento");
const infoNumeroDocumento = document.getElementById("infoNumeroDocumento");

const validarNumeroDocumento = () => {
    const valor = iNumeroDocumento.value.trim();
    const tipo = tipoDocumento.value;

    if (valor === "") {
        iNumeroDocumento.classList.add("is-invalid");
        infoNumeroDocumento.textContent = "El número de documento es obligatorio.";
        document.getElementById("labNumeroDocumento").classList.add("text-danger", "fw-bold");

        return false;
    }

    if (tipo === "DNI") {
        const regexDni = /^[0-9]{7,8}$/;

        if (!regexDni.test(valor)) {
            iNumeroDocumento.classList.add("is-invalid");
            infoNumeroDocumento.textContent = "El DNI debe tener entre 7 y 8 números.";

            return false;
        }
    }

    if (tipo === "pasaporte") {
        const regexPasaporte = /^[A-Za-z0-9]{5,15}$/;

        if (!regexPasaporte.test(valor)) {
            iNumeroDocumento.classList.add("is-invalid");
            infoNumeroDocumento.textContent = "Ingrese un pasaporte válido.";

            return false;
        }
    }

    iNumeroDocumento.classList.remove("is-invalid");
    document.getElementById("labNumeroDocumento").classList.remove("text-danger", "fw-bold");
    infoNumeroDocumento.textContent = "";

    return true;
};

iNumeroDocumento.addEventListener("blur", validarNumeroDocumento);
tipoDocumento.addEventListener("change", validarNumeroDocumento);

const iEmail = document.getElementById("email");
const infoEmail = document.getElementById("infoEmail");

const validarEmail = () => {
    const valor = iEmail.value.trim();

    if (valor === "") {
        iEmail.classList.add("is-invalid");
        infoEmail.textContent = "El email es obligatorio.";
        document.getElementById("labEmail").classList.add("text-danger", "fw-bold");

        return false;
    }

    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!regexEmail.test(valor)) {
        iEmail.classList.add("is-invalid");
        infoEmail.textContent = "Ingrese un email válido.";

        return false;
    }

    iEmail.classList.remove("is-invalid");
    document.getElementById("labEmail").classList.remove("text-danger", "fw-bold");
    infoEmail.textContent = "";

    return true;
};

iEmail.addEventListener("blur", validarEmail);


const iTelefono = document.getElementById("telefono");
const infoTelefono = document.getElementById("infoTelefono");

const validarTelefono = () => {
    const valor = iTelefono.value.trim();

    if (valor === "") {
        iTelefono.classList.add("is-invalid");
        infoTelefono.textContent = "El teléfono es obligatorio.";
        document.getElementById("labTelefono").classList.add("text-danger", "fw-bold");

        return false;
    }

    const regexTelefono = /^[0-9+\s()-]{8,20}$/;

    if (!regexTelefono.test(valor)) {
        iTelefono.classList.add("is-invalid");
        infoTelefono.textContent = "Ingrese un teléfono válido.";

        return false;
    }

    iTelefono.classList.remove("is-invalid");
    document.getElementById("labTelefono").classList.remove("text-danger", "fw-bold");
    infoTelefono.textContent = "";

    return true;
};

iTelefono.addEventListener("blur", validarTelefono);


const iFechaNacimiento = document.getElementById("fecha_nacimiento");
const infoFechaNacimiento = document.getElementById("infoFechaNacimiento");

const validarFechaNacimiento = () => {
    const valor = iFechaNacimiento.value;

    if (valor === "") {
        iFechaNacimiento.classList.add("is-invalid");
        infoFechaNacimiento.textContent = "La fecha de nacimiento es obligatoria.";
        document.getElementById("labFechaNacimiento").classList.add("text-danger", "fw-bold");

        return false;
    }

    const fechaNacimiento = new Date(valor);
    const hoy = new Date();

    let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();

    const mes = hoy.getMonth() - fechaNacimiento.getMonth();

    if (
        mes < 0 ||
        (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())
    ) {
        edad--;
    }

    if (edad < 18) {
        iFechaNacimiento.classList.add("is-invalid");
        infoFechaNacimiento.textContent = "Debe ser mayor de 18 años.";

        return false;
    }

    iFechaNacimiento.classList.remove("is-invalid");
    document.getElementById("labFechaNacimiento").classList.remove("text-danger", "fw-bold");
    infoFechaNacimiento.textContent = "";

    return true;
};

iFechaNacimiento.addEventListener("blur", validarFechaNacimiento);

const iContrasena = document.getElementById("contrasena");
const infoContrasena = document.getElementById("infoContrasena");

const validarContrasena = () => {
    const valor = iContrasena.value;

    if (valor === "") {
        iContrasena.classList.add("is-invalid");
        infoContrasena.textContent = "La contraseña es obligatoria.";
        document.getElementById("labContrasena").classList.add("text-danger", "fw-bold");

        return false;
    }

    if (valor.length < 8) {
        iContrasena.classList.add("is-invalid");
        infoContrasena.textContent = "La contraseña debe tener al menos 8 caracteres.";

        return false;
    }

    iContrasena.classList.remove("is-invalid");
    document.getElementById("labContrasena").classList.remove("text-danger", "fw-bold");
    infoContrasena.textContent = "";

    return true;
};

iContrasena.addEventListener("blur", validarContrasena);


const iConfirmarContrasena = document.getElementById("confirmar_contrasena");
const infoConfirmarContrasena = document.getElementById("infoConfirmarContrasena");

const validarConfirmarContrasena = () => {
    const valor = iConfirmarContrasena.value;

    if (valor === "") {
        iConfirmarContrasena.classList.add("is-invalid");
        infoConfirmarContrasena.textContent = "Debe confirmar la contraseña.";
        document.getElementById("labConfirmarContrasena").classList.add("text-danger", "fw-bold");

        return false;
    }

    if (valor !== iContrasena.value) {
        iConfirmarContrasena.classList.add("is-invalid");
        infoConfirmarContrasena.textContent = "Las contraseñas no coinciden.";

        return false;
    }

    iConfirmarContrasena.classList.remove("is-invalid");
    document.getElementById("labConfirmarContrasena").classList.remove("text-danger", "fw-bold");
    infoConfirmarContrasena.textContent = "";

    return true;
};

iConfirmarContrasena.addEventListener("blur", validarConfirmarContrasena);

const iAerolinea = document.getElementById("aerolinea");
const infoAerolinea = document.getElementById("infoAerolinea");

// puse este if porque estoy reusando este archivo en ambos login y cliente no tiene aerolinea

if (iAerolinea) {
const validarAerolinea = () => {
    const valor = iAerolinea.value;

    if (valor === "") {
        iAerolinea.classList.add("is-invalid");
        infoAerolinea.textContent = "Debe seleccionar una aerolínea.";
        document.getElementById("labAerolinea").classList.add("text-danger", "fw-bold");

        return false;
    }

    iAerolinea.classList.remove("is-invalid");
    document.getElementById("labAerolinea").classList.remove("text-danger", "fw-bold");
    infoAerolinea.textContent = "";

    return true;
};


iAerolinea.addEventListener("change", validarAerolinea);


const formRegistroCEO = document.getElementById("formRegistroCEO");

formRegistroCEO.addEventListener("submit", (e) => {
    const nombreValido = validarNombre();
    const apellidoValido = validarApellido();
    const documentoValido = validarNumeroDocumento();
    const emailValido = validarEmail();
    const telefonoValido = validarTelefono();
    const fechaValida = validarFechaNacimiento();
    const contrasenaValida = validarContrasena();
    const confirmarValido = validarConfirmarContrasena();
    const aerolineaValida = validarAerolinea();

    if (
        !nombreValido ||
        !apellidoValido ||
        !documentoValido ||
        !emailValido ||
        !telefonoValido ||
        !fechaValida ||
        !contrasenaValida ||
        !confirmarValido ||
        !aerolineaValida
    ) {
        e.preventDefault();
    }
});

}



const formRegistro = document.getElementById("formRegistro");

formRegistro.addEventListener("submit", (e) => {
    const nombreValido = validarNombre();
    const apellidoValido = validarApellido();
    const documentoValido = validarNumeroDocumento();
    const emailValido = validarEmail();
    const telefonoValido = validarTelefono();
    const fechaValida = validarFechaNacimiento();
    const contrasenaValida = validarContrasena();
    const confirmarValido = validarConfirmarContrasena();

    if (
        !nombreValido ||
        !apellidoValido ||
        !documentoValido ||
        !emailValido ||
        !telefonoValido ||
        !fechaValida ||
        !contrasenaValida ||
        !confirmarValido 
    ) {
        e.preventDefault();
    }
});