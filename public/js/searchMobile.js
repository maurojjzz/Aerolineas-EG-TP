// Carga los aeropuertos desde el endpoint (una sola vez)
Aeropuertos.cargar();

const options         = document.querySelectorAll(".option-line-item");
const formMobile      = document.getElementById("formMobile");
const panelVuelosMobile = document.getElementById("mobilePanelVuelos");
const panelTabsMobile = document.getElementById("mobilePanelTabs");

// Lógica de los Tabs (Mobile)
if (options.length > 0 && formMobile) {
    const mostrarPanel = (nombre) => {
        if (!panelTabsMobile) return;
        panelTabsMobile.querySelectorAll("[data-home-tab]").forEach((panel) => {
            panel.classList.toggle("d-none", panel.dataset.homeTab !== nombre);
        });
    };

    options.forEach((option) => {
        option.addEventListener("click", () => {
            options.forEach((item) => {
                item.classList.remove("active");
                item.setAttribute("aria-selected", "false");
            });
            option.classList.add("active");
            option.setAttribute("aria-selected", "true");

            const selected = option.dataset.option;

            // Ocultamos/mostramos paneles en lugar de reemplazar el HTML,
            // para no perder los listeners del formulario.
            if (selected === "vuelos") {
                (panelVuelosMobile || formMobile).classList.remove("d-none");
                if (panelTabsMobile) panelTabsMobile.classList.add("d-none");
                return;
            }

            (panelVuelosMobile || formMobile).classList.add("d-none");
            if (panelTabsMobile) {
                panelTabsMobile.classList.remove("d-none");
                mostrarPanel(selected);
            }
        });
    });
}


// Autocompletado Origen / Destino
const input        = document.getElementById("from");
const results      = document.getElementById("from-results");
const fromLocation = document.getElementById("from-location");

const toInput    = document.getElementById("to");
const toResults  = document.getElementById("to-results");
const toLocation = document.getElementById("to-location");

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

if (input) {
    input.addEventListener("input", function () {
        const text = this.value.trim();
        if (!text) {
            results.style.display = "none";
            this.setAttribute("aria-expanded", "false");
            fromLocation.textContent = "Ingrese ciudad de salida";
            return;
        }
        renderOpcionesAeropuerto(results, Aeropuertos.buscar(text), input, fromLocation, results);
    });
}

if (toInput) {
    toInput.addEventListener("input", function () {
        const text = this.value.trim();
        if (!text) {
            toResults.style.display = "none";
            this.setAttribute("aria-expanded", "false");
            toLocation.textContent = "Ingrese ciudad de destino";
            return;
        }
        const matches = Aeropuertos.buscar(text).filter(a => a.code !== input.value);
        renderOpcionesAeropuerto(toResults, matches, toInput, toLocation, toResults);
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

window.prepararListaAeropuertos(input, results);
window.prepararListaAeropuertos(toInput, toResults);

// Fechas
const meses = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

const fechaInput           = document.getElementById("fecha");
const fechaFormateada      = document.getElementById("fecha-sub");
const fechaVueltaInput     = document.getElementById("fecha-vuelta");
const fechaVueltaFormateada = document.getElementById("fecha-vuelta-sub");
const calendarVuelta       = document.getElementById("calendar-vuelta");

if (fechaInput) {
    fechaInput.addEventListener("input", function () {
        if (this.value === "") {
            fechaFormateada.textContent = "";
            if (fechaVueltaInput) {
                fechaVueltaInput.value = "";
                fechaVueltaFormateada.textContent = "";
            }
            return;
        }
        const [año, mes, dia] = this.value.split("-");
        fechaFormateada.textContent = `${parseInt(dia)} ${meses[parseInt(mes) - 1]}, ${año}`;

        if (fechaVueltaInput) {
            fechaVueltaInput.min = this.value;
            if (fechaVueltaInput.value && fechaVueltaInput.value < this.value) {
                fechaVueltaInput.value = "";
                fechaVueltaFormateada.textContent = "";
            }
        }
    });
}

if (fechaVueltaInput) {
    fechaVueltaInput.addEventListener("input", function () {
        if (this.value === "") {
            fechaVueltaFormateada.textContent = "";
            return;
        }
        const [año, mes, dia] = this.value.split("-");
        fechaVueltaFormateada.textContent = `${parseInt(dia)} ${meses[parseInt(mes) - 1]}, ${año}`;
    });
}

// Lógica para los Radio Buttons (Solo ida / Ida y vuelta)
const radiosMobile = document.querySelectorAll('input[name="tipo_viaje_radio"]');
const hiddenTipoViajeMobile = document.getElementById('mobileTipoViajeHidden');

if (radiosMobile.length > 0) {
    radiosMobile.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'ida_vuelta') {
                // Mostrar calendario de vuelta
                calendarVuelta.classList.remove('d-none');
                fechaVueltaInput.required = true;
                hiddenTipoViajeMobile.value = 'ida_vuelta';
                calendarVuelta.classList.remove('inputCalendar-bloqueado');
            } else {
                // Ocultar calendario de vuelta
                calendarVuelta.classList.add('d-none');
                fechaVueltaInput.required = false;
                hiddenTipoViajeMobile.value = 'solo_ida';
                
                fechaVueltaInput.value = '';
                fechaVueltaFormateada.textContent = '';
            }
        });
    });
}

// ELIMINAMOS la función buscarVuelosMobile() 
// Porque el botón ahora es type="submit" y el form se encarga.