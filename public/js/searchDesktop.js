Aeropuertos.cargar();

const tabs= document.querySelectorAll(".desktop-tab");
const boxContent = document.getElementsByClassName("desktop-search-form")[0];
const contenedorRadios = document.getElementById("contenedorRadiosViaje");

if (tabs.length > 0 && boxContent) {
    const copiaVuelos = boxContent.cloneNode(true);
    tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            tabs.forEach((item) => item.classList.remove("active"));
            tab.classList.add("active");

            const selected = tab.querySelector("span").textContent.toLowerCase();
            if (selected === "vuelos") {
                boxContent.innerHTML = copiaVuelos.innerHTML;
                if (contenedorRadios) contenedorRadios.classList.remove("d-none");
            }
            if (selected === "promociones") {
                boxContent.textContent = "Seleccionaste Promociones";
                if (contenedorRadios) contenedorRadios.classList.add("d-none");
            }
            // if (selected === "novedades") boxContent.textContent = "Seleccionaste Novedades";
            if (selected === "novedades") {
                if (contenedorRadios) contenedorRadios.classList.add("d-none");

                boxContent.innerHTML = `<div class="p-4 w-100 text-center text-muted">Cargando novedades...</div>`;
                
                fetch(URL_NOVEDADES_JSON)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success || !data.novedades || data.novedades.length === 0) {
                            boxContent.innerHTML = `
                                <div class=" w-100">
                                    <div class="d-flex justify-content-between align-items-center ">
                                        <a href="${URL_CLIENTE_NOVEDADES}" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                                            Ver todas las novedades →
                                        </a>
                                    </div>
                                    <p class="text-muted small m-0">No hay novedades registradas.</p>
                                </div>`;
                            return;
                        }

                        let html = `
                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-center ">
                                    <div class="d-flex align-items-center gap-2">
                                    </div> 
                                    <a href="${URL_CLIENTE_NOVEDADES}" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                                        Ver todas las novedades →
                                    </a>
                                </div>
                                <div class="d-flex flex-column gap-3" style="max-height: 380px; overflow-y: auto;">`;

                        data.novedades.forEach(nov => {
                            const fecha = new Date(nov.fechaPublicacion).toLocaleDateString('es-AR');
                            const textoSubtitulo = nov.subtitulo || nov.contenido || '';
                            
                            let imagenHtml = '';
                            if (nov.imagen && nov.imagen.trim() !== '') {
                                imagenHtml = `<img src="${nov.imagen}" alt="novedad" class="rounded-2 flex-shrink-0" style="width:70px;height:70px;object-fit:cover;">`;
                            } else {
                                imagenHtml = `
                                    <div class="rounded-2 bg-light d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width:70px;height:70px;">
                                        <i class="bi bi-megaphone fs-4"></i>
                                    </div>`;
                            }

                            html += `
                                <div class="bg-white rounded-3 border p-3 d-flex gap-3 align-items-start text-start" style="cursor:pointer;" onclick="window.location.href='${URL_CLIENTE_NOVEDAD_DETALLE}${nov.idNovedad}'">
                                    ${imagenHtml}
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <p class="fw-semibold m-0 text-truncate" style="font-size:0.9rem;">${nov.titulo}</p>
                                        <p class="text-muted m-0 mt-1 text-truncate" style="font-size:0.8rem;line-height:1.3;">${textoSubtitulo}</p>
                                        <p class="text-muted m-0 mt-1" style="font-size:0.75rem;">${fecha}</p>
                                    </div>
                                    <span class="text-muted flex-shrink-0">›</span>
                                </div>`;
                        });

                        html += `</div></div>`;
                        boxContent.innerHTML = html;
                    })
                    .catch(err => {
                        console.error("Error al cargar novedades:", err);
                        boxContent.innerHTML = `<div class="p-4 w-100 text-danger text-center">Error al cargar las novedades.</div>`;
                    });
            }
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

if (inputSalida) {
    inputSalida.addEventListener("input", function () {
        const text = this.value.trim();
        if (!text) {
            inputSalidaResult.style.display = "none";
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
            inputDestinoLocation.textContent = "Ingrese destino";
            return;
        }
        const matches = Aeropuertos.buscar(text).filter(a => a.code !== inputSalida.value);
        renderOpcionesAeropuerto(inputDestinoResult, matches, inputDestino, inputDestinoLocation, inputDestinoResult);
    });
}

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