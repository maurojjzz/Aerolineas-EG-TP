Aeropuertos.cargar();

const tabs             = document.querySelectorAll(".desktop-tab");
const panelVuelos      = document.getElementById("desktopPanelVuelos");
const panelTabs        = document.getElementById("desktopPanelTabs");
const contenedorRadios = document.getElementById("contenedorRadiosViaje");

if (tabs.length > 0 && panelVuelos && panelTabs) {
    const mostrarPanel = (nombre) => {
        panelTabs.querySelectorAll("[data-home-tab]").forEach((panel) => {
            panel.classList.toggle("d-none", panel.dataset.homeTab !== nombre);
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            tabs.forEach((item) => {
                item.classList.remove("active");
                item.setAttribute("aria-selected", "false");
            });
            tab.classList.add("active");
            tab.setAttribute("aria-selected", "true");

            const selected = tab.dataset.tab;
            if (!selected) return;

            // Volvemos al buscador: no se reemplaza el HTML, así no se pierden
            // los listeners del autocompletado ni de las fechas.
            if (selected === "vuelos") {
                panelVuelos.classList.remove("d-none");
                if (contenedorRadios) contenedorRadios.classList.remove("d-none");
                panelTabs.classList.add("d-none");
                return;
            }

            panelVuelos.classList.add("d-none");
            if (contenedorRadios) contenedorRadios.classList.add("d-none");
            panelTabs.classList.remove("d-none");
            mostrarPanel(selected);
        });
    });
}

// Autocompletado
const inputSalida          = document.getElementById("from-desktop");
const inputSalidaResult    = document.getElementById("from-results-desktop");
const inputSalidaLocation  = document.getElementById("from-location-desktop");

const inputDestino         = document.getElementById("to-desktop");
const inputDestinoResult   = document.getElementById("to-results-desktop");
const inputDestinoLocation = document.getElementById("to-location-desktop");

function renderOpcionesAeropuerto(container, matches, inputEl, locationEl, resultsEl) {
    container.innerHTML = "";

    const elegir = (aeropuerto) => {
        inputEl.value = aeropuerto.code;
        locationEl.textContent = `${aeropuerto.city}, ${aeropuerto.country}`;
        resultsEl.style.display = "none";
        inputEl.setAttribute("aria-expanded", "false");
        inputEl.focus();
    };

    matches.forEach((a, i) => {
        const div = document.createElement("div");
        div.classList.add("autocomplete-option");
        div.setAttribute("role", "option");
        div.setAttribute("aria-selected", "false");
        div.setAttribute("id", `${container.id}-opcion-${i}`);
        div.tabIndex = -1;
        div.innerHTML = `<strong>${a.code}</strong><span>${a.city}, ${a.country}</span>`;

        div.addEventListener("click", () => elegir(a));
        div.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                elegir(a);
            } else if (event.key === "ArrowDown") {
                event.preventDefault();
                (div.nextElementSibling || div).focus();
            } else if (event.key === "ArrowUp") {
                event.preventDefault();
                (div.previousElementSibling || inputEl).focus();
            } else if (event.key === "Escape") {
                resultsEl.style.display = "none";
                inputEl.setAttribute("aria-expanded", "false");
                inputEl.focus();
            }
        });
        container.appendChild(div);
    });
    resultsEl.style.display = matches.length ? "block" : "none";
    inputEl.setAttribute("aria-expanded", matches.length ? "true" : "false");
}

if (inputSalida) {
    inputSalida.addEventListener("input", function () {
        const text = this.value.trim();
        if (!text) {
            inputSalidaResult.style.display = "none";
            this.setAttribute("aria-expanded", "false");
            inputSalidaLocation.textContent = "Ingrese salida";
            return;
        }
        renderOpcionesAeropuerto(inputSalidaResult, Aeropuertos.buscar(text), inputSalida, inputSalidaLocation, inputSalidaResult);
    });
}

if (inputDestino) {
    inputDestino.addEventListener("input", function () {
        const text = this.value.trim();
        if (!text) {
            inputDestinoResult.style.display = "none";
            this.setAttribute("aria-expanded", "false");
            inputDestinoLocation.textContent = "Ingrese destino";
            return;
        }
        const matches = Aeropuertos.buscar(text).filter(a => a.code !== inputSalida.value);
        renderOpcionesAeropuerto(inputDestinoResult, matches, inputDestino, inputDestinoLocation, inputDestinoResult);
    });
}

// Accesibilidad del autocompletado: combobox con lista de opciones navegable
window.prepararListaAeropuertos = function (inputEl, resultadosEl) {
    if (!inputEl || !resultadosEl || inputEl.dataset.listaPreparada === "1") return;
    inputEl.dataset.listaPreparada = "1";
    inputEl.setAttribute("role", "combobox");
    inputEl.setAttribute("aria-autocomplete", "list");
    inputEl.setAttribute("aria-expanded", "false");
    inputEl.setAttribute("aria-controls", resultadosEl.id);
    resultadosEl.setAttribute("role", "listbox");

    inputEl.addEventListener("keydown", function (event) {
        if (event.key === "ArrowDown") {
            const primera = resultadosEl.querySelector('[role="option"]');
            if (primera) {
                event.preventDefault();
                primera.focus();
            }
        } else if (event.key === "Escape") {
            resultadosEl.style.display = "none";
            inputEl.setAttribute("aria-expanded", "false");
        }
    });
};

window.prepararListaAeropuertos(inputSalida, inputSalidaResult);
window.prepararListaAeropuertos(inputDestino, inputDestinoResult);

const swapButton = document.getElementById("swap-desktop");
if (swapButton) {
    swapButton.addEventListener("click", function () {
        const temp = inputSalida.value;
        inputSalida.value = inputDestino.value;
        inputDestino.value = temp;

        const tempLocation = inputSalidaLocation.textContent;
        inputSalidaLocation.textContent = inputDestinoLocation.textContent;
        inputDestinoLocation.textContent = tempLocation;

        inputSalida.dispatchEvent(new Event("input"));
        inputDestino.dispatchEvent(new Event("input"));
        inputSalidaResult.style.display  = "none";
        inputDestinoResult.style.display = "none";
    });
}

const mesesDesktop = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

const dateInput            = document.getElementById("fecha-desktop");
const dateInputWord        = document.getElementById("fecha-sub-desktop");
const dateInputReturn      = document.getElementById("fecha-vuelta-desktop");
const dateInputReturnWord  = document.getElementById("fecha-vuelta-sub-desktop");

if (dateInput) {
    dateInput.addEventListener("input", function () {
        if (this.value === "") {
            dateInputWord.textContent = "Seleccione fecha";
            if (dateInputReturn) dateInputReturn.min = "";
            return;
        }
        const [año, mes, dia] = this.value.split("-");
        dateInputWord.textContent = `${parseInt(dia)} ${mesesDesktop[parseInt(mes) - 1]}, ${año}`;
        
        if (dateInputReturn) {
            dateInputReturn.min = this.value;
            if (dateInputReturn.value && dateInputReturn.value < this.value) {
                dateInputReturn.value = "";
                dateInputReturnWord.textContent = "Seleccione fecha";
            }
        }
    });
}

if (dateInputReturn) {
    dateInputReturn.addEventListener("input", function () {
        if (this.value === "") {
            dateInputReturnWord.textContent = "";
            return;
        }
        const [año, mes, dia] = this.value.split("-");
        dateInputReturnWord.textContent = `${parseInt(dia)} ${mesesDesktop[parseInt(mes) - 1]}, ${año}`;
    });
}

// ── LÓGICA DE RADIO BUTTONS A PRUEBA DE BALAS ──
// Usamos 'document.addEventListener' para que funcione incluso si los tabs clonan el formulario.
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'tipo_viaje_desktop') {
        const contenedorVuelta = document.getElementById('contenedorFechaVueltaDesktop');
        const dividerVuelta = document.getElementById('dividerVueltaDesktop');
        const inputVuelta = document.getElementById('fecha-vuelta-desktop');
        const hiddenTipoViaje = document.getElementById('desktopTipoViajeHidden');

        if (e.target.value === 'ida_vuelta') {
            if (contenedorVuelta) {
                contenedorVuelta.classList.remove('d-none');
                contenedorVuelta.style.display = 'flex'; // Forzamos flex para que no se aplaste
                contenedorVuelta.classList.add('flex-column'); // Aseguramos que sea columna
            }
            if (dividerVuelta) {
                dividerVuelta.classList.remove('d-none');
                dividerVuelta.style.display = 'block';
            }
            if (inputVuelta) inputVuelta.required = true;
            if (hiddenTipoViaje) hiddenTipoViaje.value = 'ida_vuelta';
        } else {
            if (contenedorVuelta) {
                contenedorVuelta.classList.add('d-none');
                contenedorVuelta.style.display = ''; // Limpiamos
            }
            if (dividerVuelta) {
                dividerVuelta.classList.add('d-none');
                dividerVuelta.style.display = '';
            }
            if (inputVuelta) {
                inputVuelta.required = false;
                inputVuelta.value = '';
            }
            if (dateInputReturnWord) dateInputReturnWord.textContent = "Seleccione fecha";
            if (hiddenTipoViaje) hiddenTipoViaje.value = 'solo_ida';
        }
    }
});