// Carga los aeropuertos desde el endpoint (una sola vez)
Aeropuertos.cargar();

const options = document.querySelectorAll(".option-line-item");
const box2 = document.getElementById("box2");

const copiaVuelosMob = box2.cloneNode(true);

options.forEach((option) => {
    option.addEventListener("click", () => {
        options.forEach((item) => item.classList.remove("active"));
        option.classList.add("active");

        const selected = option.dataset.option;

        if (selected === "vuelos") {
            box2.innerHTML = copiaVuelosMob.innerHTML;
        }
        if (selected === "promociones") {
            box2.textContent = "Seleccionaste Promociones";
        }
        if (selected === "novedades") {
            box2.textContent = "Seleccionaste Novedades";
        }
    });
});

const input        = document.getElementById("from");
const results      = document.getElementById("from-results");
const fromLocation = document.getElementById("from-location");

const toInput    = document.getElementById("to");
const toResults  = document.getElementById("to-results");
const toLocation = document.getElementById("to-location");

function renderOpcionesAeropuerto(container, matches, inputEl, locationEl, resultsEl) {
    container.innerHTML = "";

    matches.forEach(a => {
        const div = document.createElement("div");
        div.classList.add("autocomplete-option");
        div.innerHTML = `<strong>${a.code}</strong><span>${a.city}, ${a.country}</span>`;

        div.addEventListener("click", () => {
            inputEl.value = a.code;
            locationEl.textContent = `${a.city}, ${a.country}`;
            resultsEl.style.display = "none";
        });

        container.appendChild(div);
    });

    resultsEl.style.display = matches.length ? "block" : "none";
}

input.addEventListener("input", function () {
    const text = this.value.trim();

    if (!text) {
        results.style.display = "none";
        fromLocation.textContent = "Ingrese ciudad de salida";
        return;
    }

    renderOpcionesAeropuerto(results, Aeropuertos.buscar(text), input, fromLocation, results);
});

toInput.addEventListener("input", function () {
    const text = this.value.trim();

    if (!text) {
        toResults.style.display = "none";
        toLocation.textContent = "Ingrese ciudad de destino";
        return;
    }

    const matches = Aeropuertos.buscar(text).filter(a => a.code !== input.value);
    renderOpcionesAeropuerto(toResults, matches, toInput, toLocation, toResults);
});

// ── Fechas ───────────────────────────────────────────────────────────────────
const meses = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

const fechaInput           = document.getElementById("fecha");
const fechaFormateada      = document.getElementById("fecha-sub");
const fechaVueltaInput     = document.getElementById("fecha-vuelta");
const fechaVueltaFormateada = document.getElementById("fecha-vuelta-sub");
const calendarVuelta       = document.getElementById("calendar-vuelta");

let vueltaBloqueada = true;

fechaInput.addEventListener("input", function () {
    if (this.value === "") {
        fechaFormateada.textContent = "";
        vueltaBloqueada = true;
        calendarVuelta.classList.add("inputCalendar-bloqueado");
        fechaVueltaInput.value = "";
        fechaVueltaFormateada.textContent = "";
        return;
    }

    const [año, mes, dia] = this.value.split("-");
    fechaFormateada.textContent = `${parseInt(dia)} ${meses[parseInt(mes) - 1]}, ${año}`;

    vueltaBloqueada = false;
    calendarVuelta.classList.remove("inputCalendar-bloqueado");
    fechaVueltaInput.min = this.value;

    if (fechaVueltaInput.value && fechaVueltaInput.value < this.value) {
        fechaVueltaInput.value = "";
        fechaVueltaFormateada.textContent = "";
    }
});

fechaVueltaInput.addEventListener("click", function (event) {
    if (vueltaBloqueada) {
        event.preventDefault();
        this.blur();
    }
});

fechaVueltaInput.addEventListener("keydown", function (event) {
    if (vueltaBloqueada) event.preventDefault();
});

fechaVueltaInput.addEventListener("input", function () {
    if (vueltaBloqueada) {
        this.value = "";
        fechaVueltaFormateada.textContent = "";
        return;
    }

    if (this.value === "") {
        fechaVueltaFormateada.textContent = "";
        return;
    }

    const [año, mes, dia] = this.value.split("-");
    fechaVueltaFormateada.textContent = `${parseInt(dia)} ${meses[parseInt(mes) - 1]}, ${año}`;
});

// ── Buscar (se llama desde el botón) ─────────────────────────────────────────
function buscarVuelosMobile() {
    const origen    = document.getElementById("from").value.trim();
    const destino   = document.getElementById("to").value.trim();
    const fecha     = document.getElementById("fecha").value;
    const pasajeros = document.getElementById("passengers").value;

    if (!origen || !destino || !fecha) {
        alert("Por favor completá origen, destino y fecha.");
        return;
    }

    const params = new URLSearchParams({
        pagina: 'buscar-vuelos',
        origen,
        destino,
        fecha,
        pasajeros
    });

    window.location.href = 'index.php?' + params.toString();
}