const iEmail = document.getElementById("email");
const infoEmail = document.getElementById("infoEmail");

const validarEmail = () => {
    const valor = iEmail.value.trim();

    if (valor === "") {
        iEmail.classList.add("is-invalid");
        infoEmail.textContent = "El email es obligatorio.";
        infoEmail.classList.add("text-danger");
        document.getElementById("labEmail").classList.add("text-danger", "fw-bold");
        return false;
    }

    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!regexEmail.test(valor)) {
        iEmail.classList.add("is-invalid");
        infoEmail.textContent = "Ingrese un email válido.";
        infoEmail.classList.add("text-danger");
        document.getElementById("labEmail").classList.add("text-danger", "fw-bold");
        return false;
    }

    iEmail.classList.remove("is-invalid");
    infoEmail.classList.remove("text-danger");
    document.getElementById("labEmail").classList.remove("text-danger", "fw-bold");
    infoEmail.textContent = "";
    return true;
};

iEmail.addEventListener("blur", validarEmail);


const iContrasena = document.getElementById("contrasena");
const infoContrasena = document.getElementById("infoContrasena");

const validarContrasena = () => {
    const valor = iContrasena.value;

    if (valor === "") {
        iContrasena.classList.add("is-invalid");
        infoContrasena.textContent = "La contraseña es obligatoria.";
        infoContrasena.classList.add("text-danger");
        document.getElementById("labContrasena").classList.add("text-danger", "fw-bold");
        return false;
    }

    if (valor.length < 8) {
        iContrasena.classList.add("is-invalid");
        infoContrasena.textContent = "La contraseña debe tener al menos 8 caracteres.";
        infoContrasena.classList.add("text-danger");
        document.getElementById("labContrasena").classList.add("text-danger", "fw-bold");
        return false;
    }

    iContrasena.classList.remove("is-invalid");
    infoContrasena.classList.remove("text-danger");
    document.getElementById("labContrasena").classList.remove("text-danger", "fw-bold");
    infoContrasena.textContent = "";
    return true;
};

iContrasena.addEventListener("blur", validarContrasena);


const formLogin = document.querySelector("form[action*='accion=login']");

if (formLogin) {
    formLogin.addEventListener("submit", (e) => {
        const emailValido = validarEmail();
        const contrasenaValida = validarContrasena();

        if (!emailValido || !contrasenaValida) {
            e.preventDefault();
        }
    });
}