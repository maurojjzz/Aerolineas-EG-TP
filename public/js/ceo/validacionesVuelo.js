// ── Elementos ────────────────────────────────────────────────────────────────
const iOrigen       = document.getElementById("origen");
const iDestino      = document.getElementById("destino");
const iCodOrigen    = document.getElementById("codigo_origen");
const iCodDestino   = document.getElementById("codigo_destino");
const iFecha        = document.getElementById("fecha");
const iHora         = document.getElementById("hora");
const iFechaLlegada = document.getElementById("fecha_llegada");
const iHoraLlegada  = document.getElementById("hora_llegada");
const iAsientos     = document.getElementById("asientos_totales");
const iPrecio       = document.getElementById("precio");

const infoOrigen       = document.getElementById("infoOrigen");
const infoDestino      = document.getElementById("infoDestino");
const infoFecha        = document.getElementById("infoFecha");
const infoHora         = document.getElementById("infoHora");
const infoFechaLlegada = document.getElementById("infoFechaLlegada");
const infoHoraLlegada  = document.getElementById("infoHoraLlegada");
const infoAsientos     = document.getElementById("infoAsientos");
const infoPrecio       = document.getElementById("infoPrecio");

// ── Caja de errores ──────────────────────────────────────────────────────────
const errorBox           = document.getElementById("erroresBox");
const recomendacionesBox = document.getElementById("recomendacionesBox");
const cantidadErrores    = document.getElementById("cantidadErrores");
const listaErrores       = document.getElementById("errorList");

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

// ── Catálogo de aeropuertos (mismo formato que el datalist) ──────────────────
const aeropuertosFormato = new Map();
// Se llena apenas carga la página con los <option> del datalist
document.querySelectorAll("#lista-aeropuertos option").forEach(opt => {
    const valor = opt.value;                       // "Buenos Aires (EZE)"
    const match = valor.match(/^(.+)\s\(([A-Z]{3})\)$/);
    if (match) {
        const ciudad = match[1];
        const code   = match[2];
        aeropuertosFormato.set(valor.toLowerCase(), { ciudad, code });
    }
});

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

    const match = aeropuertosFormato.get(valor.toLowerCase());

    if (!match) {
        iOrigen.classList.add("is-invalid");
        infoOrigen.textContent = "Seleccioná un aeropuerto de la lista.";
        infoOrigen.classList.add("text-danger");
        agregarError("origen", "Origen", "debe ser un aeropuerto de la lista.");
        return false;
    }

    // Setear el hidden con el código
    iCodOrigen.value = match.code;

    iOrigen.classList.remove("is-invalid");
    infoOrigen.classList.remove("text-danger");
    infoOrigen.textContent = `Origen: ${match.ciudad} (${match.code})`;
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

    const match = aeropuertosFormato.get(valor.toLowerCase());

    if (!match) {
        iDestino.classList.add("is-invalid");
        infoDestino.textContent = "Seleccioná un aeropuerto de la lista.";
        infoDestino.classList.add("text-danger");
        agregarError("destino", "Destino", "debe ser un aeropuerto de la lista.");
        return false;
    }

    // Comparar con el código de origen (no con el texto)
    if (iCodOrigen.value && match.code === iCodOrigen.value) {
        iDestino.classList.add("is-invalid");
        infoDestino.textContent = "El destino no puede ser el mismo aeropuerto que el origen.";
        infoDestino.classList.add("text-danger");
        agregarError("destino", "Destino", "no puede ser el mismo aeropuerto que el origen.");
        return false;
    }

    iCodDestino.value = match.code;

    iDestino.classList.remove("is-invalid");
    infoDestino.classList.remove("text-danger");
    infoDestino.textContent = `Destino: ${match.ciudad} (${match.code})`;
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

    const hora = iHora.value || "00:00";
    const fechaHora = new Date(valor + "T" + hora);

    if (fechaHora.getTime() < Date.now()) {
        iFecha.classList.add("is-invalid");
        infoFecha.textContent = "La fecha y hora no pueden ser en el pasado.";
        infoFecha.classList.add("text-danger");
        agregarError("fecha", "Fecha", "no puede ser en el pasado.");
        return false;
    }

    // Actualizar min de la fecha de llegada
    if (iFechaLlegada) {
        iFechaLlegada.min = valor;
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

const validarLlegada = () => {
    if (!iFechaLlegada || !iHoraLlegada) return true;

    const fechaL = iFechaLlegada.value;
    const horaL  = iHoraLlegada.value;

    // Ambos vacíos: válido (es opcional)
    if (!fechaL && !horaL) {
        if (infoFechaLlegada) {
            infoFechaLlegada.classList.remove("text-danger");
            infoFechaLlegada.textContent = "Debe ser posterior a la salida.";
        }
        quitarError("llegada");
        return true;
    }

    // Solo uno lleno: error
    if ((fechaL && !horaL) || (!fechaL && horaL)) {
        if (infoFechaLlegada) {
            infoFechaLlegada.textContent = "Si ingresás llegada, completá fecha Y hora.";
            infoFechaLlegada.classList.add("text-danger");
        }
        agregarError("llegada", "Llegada", "debe tener fecha y hora si se completa.");
        return false;
    }

    // Ambos llenos: verificar posterior a salida
    if (iFecha.value && iHora.value) {
        const salida  = new Date(iFecha.value + "T" + iHora.value);
        const llegada = new Date(fechaL + "T" + horaL);

        if (llegada <= salida) {
            if (infoFechaLlegada) {
                infoFechaLlegada.textContent = "La llegada debe ser posterior a la salida.";
                infoFechaLlegada.classList.add("text-danger");
            }
            agregarError("llegada", "Llegada", "debe ser posterior a la salida.");
            return false;
        }
    }

    if (infoFechaLlegada) {
        infoFechaLlegada.classList.remove("text-danger");
        infoFechaLlegada.textContent = "Debe ser posterior a la salida.";
    }
    quitarError("llegada");
    return true;
};

// ── Listeners ────────────────────────────────────────────────────────────────
iOrigen.addEventListener("blur", validarOrigen);
iDestino.addEventListener("blur", validarDestino);
iFecha.addEventListener("change", validarFecha);
iHora.addEventListener("change", validarHora);
iAsientos.addEventListener("blur", validarAsientos);
iPrecio.addEventListener("blur", validarPrecio);

if (iFechaLlegada) iFechaLlegada.addEventListener("change", validarLlegada);
if (iHoraLlegada)  iHoraLlegada.addEventListener("change",  validarLlegada);

// Actualizar también el "min" de la fecha de llegada cuando cambia la fecha de salida
if (iFecha && iFechaLlegada) {
    iFecha.addEventListener("change", function () {
        iFechaLlegada.min = this.value || "";
    });
}

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
        const llegadaOk  = validarLlegada();

        if (!origenOk || !destinoOk || !fechaOk || !horaOk
            || !asientosOk || !precioOk || !llegadaOk) {
            e.preventDefault();
        }
    });
}