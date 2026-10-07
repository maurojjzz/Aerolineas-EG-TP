// ── Elementos ────────────────────────────────────────────────────────────────
const iOrigen   = document.getElementById("origen");
const iDestino  = document.getElementById("destino");
const iFecha    = document.getElementById("fecha");
const iHora     = document.getElementById("hora");
const iAsientos = document.getElementById("asientos");
const iPrecio   = document.getElementById("precio");

const infoOrigen   = document.getElementById("infoOrigen");
const infoDestino  = document.getElementById("infoDestino");
const infoFecha    = document.getElementById("infoFecha");
const infoHora     = document.getElementById("infoHora");
const infoAsientos = document.getElementById("infoAsientos");
const infoPrecio   = document.getElementById("infoPrecio");

// ── Caja de errores (misma lógica que validaciones.js de aerolínea) ──────────
const errorBox          = document.getElementById("erroresBox");
const recomendacionesBox = document.getElementById("recomendacionesBox");
const cantidadErrores   = document.getElementById("cantidadErrores");
const listaErrores      = document.getElementById("errorList");

const errores = {};

const agregarError = (campo, titulo, mensaje) => {
    errores[campo] = { titulo, mensaje };
    actualizarCajaErrores();
};

const quitarError = (campo) => {
    delete errores[campo];
    actualizarCajaErrores();
};

const actualizarCajaErrores = () => {
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

// ── Validaciones ─────────────────────────────────────────────────────────────
const validarOrigen = () => {
    const valor = iOrigen.value.trim();
    if (valor === "") {
        iOrigen.classList.add("is-invalid");
        infoOrigen.textContent = "El origen es obligatorio.";
        infoOrigen.classList.add("text-danger");
        agregarError("origen", "Origen", "es obligatorio.");
        return false;
    }
    if (valor.length < 3 || valor.length > 100) {
        iOrigen.classList.add("is-invalid");
        infoOrigen.textContent = "El origen debe tener entre 3 y 100 caracteres.";
        infoOrigen.classList.add("text-danger");
        agregarError("origen", "Origen", "debe tener entre 3 y 100 caracteres.");
        return false;
    }
    iOrigen.classList.remove("is-invalid");
    infoOrigen.classList.remove("text-danger");
    infoOrigen.textContent = "Ciudad o aeropuerto de salida.";
    quitarError("origen");
    return true;
};

const validarDestino = () => {
    const valor = iDestino.value.trim();
    if (valor === "") {
        iDestino.classList.add("is-invalid");
        infoDestino.textContent = "El destino es obligatorio.";
        infoDestino.classList.add("text-danger");
        agregarError("destino", "Destino", "es obligatorio.");
        return false;
    }
    if (valor.length < 3 || valor.length > 100) {
        iDestino.classList.add("is-invalid");
        infoDestino.textContent = "El destino debe tener entre 3 y 100 caracteres.";
        infoDestino.classList.add("text-danger");
        agregarError("destino", "Destino", "debe tener entre 3 y 100 caracteres.");
        return false;
    }
    if (iOrigen.value.trim() && valor.toLowerCase() === iOrigen.value.trim().toLowerCase()) {
        iDestino.classList.add("is-invalid");
        infoDestino.textContent = "El destino no puede ser igual al origen.";
        infoDestino.classList.add("text-danger");
        agregarError("destino", "Destino", "no puede ser igual al origen.");
        return false;
    }
    iDestino.classList.remove("is-invalid");
    infoDestino.classList.remove("text-danger");
    infoDestino.textContent = "Ciudad o aeropuerto de llegada.";
    quitarError("destino");
    return true;
};

const validarFecha = () => {
    const valor = iFecha.value;
    if (valor === "") {
        iFecha.classList.add("is-invalid");
        infoFecha.textContent = "La fecha es obligatoria.";
        infoFecha.classList.add("text-danger");
        agregarError("fecha", "Fecha", "es obligatoria.");
        return false;
    }
    // Comparar fecha + hora con ahora
    const hora = iHora.value || "00:00";
    const fechaHora = new Date(valor + "T" + hora);
    if (fechaHora.getTime() < Date.now()) {
        iFecha.classList.add("is-invalid");
        infoFecha.textContent = "La fecha y hora no pueden ser en el pasado.";
        infoFecha.classList.add("text-danger");
        agregarError("fecha", "Fecha", "no puede ser en el pasado.");
        return false;
    }
    iFecha.classList.remove("is-invalid");
    infoFecha.classList.remove("text-danger");
    infoFecha.textContent = "";
    quitarError("fecha");
    return true;
};

const validarHora = () => {
    const valor = iHora.value;
    if (valor === "") {
        iHora.classList.add("is-invalid");
        infoHora.textContent = "La hora es obligatoria.";
        infoHora.classList.add("text-danger");
        agregarError("hora", "Hora", "es obligatoria.");
        return false;
    }
    // Si ya hay fecha, revalidamos fecha por si la hora cambió
    if (iFecha.value) validarFecha();
    iHora.classList.remove("is-invalid");
    infoHora.classList.remove("text-danger");
    infoHora.textContent = "";
    quitarError("hora");
    return true;
};

const validarAsientos = () => {
    const valor = iAsientos.value.trim();
    const num = Number(valor);
    if (valor === "" || num <= 0) {
        iAsientos.classList.add("is-invalid");
        infoAsientos.textContent = "Los asientos deben ser un número positivo.";
        infoAsientos.classList.add("text-danger");
        agregarError("asientos", "Asientos", "deben ser un número positivo.");
        return false;
    }
    if (num > 1000) {
        iAsientos.classList.add("is-invalid");
        infoAsientos.textContent = "El máximo permitido es 1000 asientos.";
        infoAsientos.classList.add("text-danger");
        agregarError("asientos", "Asientos", "no pueden superar los 1000.");
        return false;
    }
    iAsientos.classList.remove("is-invalid");
    infoAsientos.classList.remove("text-danger");
    infoAsientos.textContent = "Cantidad de asientos a la venta.";
    quitarError("asientos");
    return true;
};

const validarPrecio = () => {
    const valor = iPrecio.value.trim();
    const num = Number(valor);
    if (valor === "" || num <= 0) {
        iPrecio.classList.add("is-invalid");
        infoPrecio.textContent = "El precio debe ser un número positivo.";
        infoPrecio.classList.add("text-danger");
        agregarError("precio", "Precio", "debe ser un número positivo.");
        return false;
    }
    if (num > 99999999.99) {
        iPrecio.classList.add("is-invalid");
        infoPrecio.textContent = "El precio es demasiado alto.";
        infoPrecio.classList.add("text-danger");
        agregarError("precio", "Precio", "es demasiado alto.");
        return false;
    }
    iPrecio.classList.remove("is-invalid");
    infoPrecio.classList.remove("text-danger");
    infoPrecio.textContent = "Precio base por pasajero.";
    quitarError("precio");
    return true;
};

// ── Listeners ────────────────────────────────────────────────────────────────
iOrigen.addEventListener("blur", validarOrigen);
iDestino.addEventListener("blur", validarDestino);
iFecha.addEventListener("change", validarFecha);
iHora.addEventListener("change", validarHora);
iAsientos.addEventListener("blur", validarAsientos);
iPrecio.addEventListener("blur", validarPrecio);

// ── Submit ───────────────────────────────────────────────────────────────────
const formVuelo = document.getElementById("formVuelo");
if (formVuelo) {
    formVuelo.addEventListener("submit", (e) => {
        const origenOk   = validarOrigen();
        const destinoOk  = validarDestino();
        const fechaOk    = validarFecha();
        const horaOk     = validarHora();
        const asientosOk = validarAsientos();
        const precioOk   = validarPrecio();

        if (!origenOk || !destinoOk || !fechaOk || !horaOk || !asientosOk || !precioOk) {
            e.preventDefault();
        }
    });
}