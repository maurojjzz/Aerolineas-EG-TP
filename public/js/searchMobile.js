// Carga los aeropuertos desde el endpoint (una sola vez)
Aeropuertos.cargar();

const options = document.querySelectorAll(".option-line-item");
const box2 = document.getElementById("box2");

// Lógica de los Tabs (Mobile)
if (options.length > 0 && box2) {
    const copiaVuelosMob = box2.cloneNode(true);
    options.forEach((option) => {
        option.addEventListener("click", () => {
            options.forEach((item) => item.classList.remove("active"));
            option.classList.add("active");

            const selected = option.dataset.option;
            if (selected === "vuelos") box2.innerHTML = copiaVuelosMob.innerHTML;
            // if (selected === "promociones") box2.textContent = "Seleccionaste Promociones";
            if (selected === "promociones") {
                if (contenedorRadios) contenedorRadios.classList.add("d-none");
                box2.innerHTML = `<div class="p-4 w-100 text-center text-muted">Cargando promociones...</div>`;

                fetch(URL_PROMOCIONES_JSON)
                    .then(res => res.json())
                    .then(data => {
                        console.log("Datos recibidos del servidor:", data);

                        if (!data.success || !data.promociones || data.promociones.length === 0) {
                            box2.innerHTML = `
                                <div class="w-100 p-3">
                                    <div class="d-flex justify-content-end align-items-center mb-2">
                                        <a href="${URL_CLIENTE_PROMOCIONES}" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                                            Ver todas las promociones →
                                        </a>
                                    </div>
                                    <p class="text-muted small m-0 text-center">No hay promociones registradas.</p>
                                </div>`;
                            return;
                        }

                        let html = `
                            <div class="w-100 p-2">
                                <div class="d-flex justify-content-end align-items-center mb-2">
                                    <a href="${URL_CLIENTE_PROMOCIONES}" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                                        Ver todas las promociones →
                                    </a>
                                </div>
                                <div class="d-flex flex-column gap-3" style="max-height: 380px; overflow-y: auto;">`;

                        data.promociones.forEach(promo => {
                            const titulo = promo.nombre || 'Promoción Especial';
                            const codigo = promo.codigo || '';
                            const aerolinea = promo.nombreAerolinea || 'Aerolínea';
                            
                            const porcentaje = promo.descuentoPorcentaje ? Math.round(promo.descuentoPorcentaje) : 0;
                            const textoDescuento = porcentaje > 0 ? `${porcentaje}%` : 'PROMO';
                            
                            const fInicio = promo.fechaInicio ? promo.fechaInicio.split('-').reverse().join('/') : '';
                            const fFin = promo.fechaFin ? promo.fechaFin.split('-').reverse().join('/') : '';

                            html += `
                                <div class="bg-white rounded-3 border p-2 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 shadow-sm" style="border-left: 5px solid #048d42 !important;">
                                    
                                    <!-- 1. Descuento -->
                                    <div class="rounded-3 text-white d-flex flex-column align-items-center justify-content-center text-center p-2 flex-shrink-0 w-100 w-sm-auto" style="min-width: 90px; min-height: 75px; background-color: #048d42;">
                                        <span class="fw-bold" style="font-size: 1.5rem; line-height: 1.1;">${textoDescuento}</span>
                                        <span class="text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.9;">OFF</span>
                                    </div>

                                    <!-- 2. Título y Código -->
                                    <div class="flex-grow-1 text-center text-sm-start" style="min-width: 0;">
                                        <h5 class="fw-bold text-dark m-0 text-truncate" style="font-size: 1.2rem;">${titulo}</h5>
                                        <div class="d-flex justify-content-center justify-content-sm-start align-items-center gap-2 mt-1">
                                            <span class="badge bg-light text-secondary border fw-semibold" style="font-size: 0.7rem;">${codigo}</span>
                                        </div>
                                    </div>

                                    <!-- 3. Aerolínea y Fechas -->
                                    <div class="d-flex flex-column text-center text-sm-start px-2 border-top border-sm-top-0 border-sm-start pt-2 pt-sm-0 text-nowrap" style="font-size: 0.85rem;">
                                        <span class="text-muted mb-1">Aerolínea: <strong class="text-dark">${aerolinea}</strong></span>
                                        <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-1 text-muted">
                                            <i class="bi bi-calendar-check text-success"></i>
                                            <span>Del <strong>${fInicio}</strong> al <strong>${fFin}</strong></span>
                                        </div>
                                    </div>
                                </div>`;
                        });

                        html += `</div></div>`;
                        box2.innerHTML = html;
                    })
                    .catch(err => {
                        console.error("Error al cargar promociones:", err);
                        box2.innerHTML = `<div class="p-4 w-100 text-danger text-center">Error al cargar las promociones.</div>`;
                    });
            }
            
            if (selected === "novedades") {
                if (contenedorRadios) contenedorRadios.classList.add("d-none");
                box2.innerHTML = `<div class="p-4 w-100 text-center text-muted">Cargando novedades...</div>`;
                
                fetch(URL_NOVEDADES_JSON)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success || !data.novedades || data.novedades.length === 0) {
                            box2.innerHTML = `
                                <div class="w-100 p-3">
                                    <div class="d-flex justify-content-end align-items-center mb-2">
                                        <a href="${URL_CLIENTE_NOVEDADES}" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                                            Ver todas las novedades →
                                        </a>
                                    </div>
                                    <p class="text-muted small m-0 text-center">No hay novedades registradas.</p>
                                </div>`;
                            return;
                        }

                        let html = `
                            <div class="w-100 p-2">
                                <div class="d-flex justify-content-end align-items-center mb-2">
                                    <a href="${URL_CLIENTE_NOVEDADES}" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                                        Ver todas las novedades →
                                    </a>
                                </div>
                                <div class="d-flex flex-column gap-3" style="max-height: 380px; overflow-y: auto;">`;

                        data.novedades.forEach(nov => {
                            const titulo = nov.titulo || 'Novedad Especial';
                            const subtitulo = nov.subtitulo || nov.descripcion || '';
                            const categoria = nov.categoria || 'Novedad';
                            
                            // Formatear fecha de YYYY-MM-DD a DD/MM/YYYY
                            const fecha = nov.fechaPublicacion ? nov.fechaPublicacion.split('-')[0].split('-').reverse().join('/') : (nov.fechaPublicacion ? nov.fechaPublicacion.split(' ')[0].split('-').reverse().join('/') : '');

                            // Imagen o caja por defecto (tamaño compacto para mobile)
                            const imagenHtml = nov.imagen 
                                ? `<img src="${nov.imagen}" alt="Novedad" class="rounded-3" style="width: 70px; height: 60px; object-fit: cover;">`
                                : `<div class="rounded-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 70px; height: 60px; background-color: #0d6efd;">
                                    <i class="bi bi-megaphone fs-5"></i>
                                </div>`;

                            html += `
                                <div class="bg-white rounded-3 border p-2 d-flex flex-column gap-2 shadow-sm" style="border-left: 5px solid #0d6efd !important; cursor: pointer;" onclick="window.location.href='${URL_CLIENTE_NOVEDAD_DETALLE}${nov.idNovedad}'">
                                    
                                    <!-- Fila superior: Imagen + Categoría y Título -->
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0">
                                            ${imagenHtml}
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="mb-1">
                                                <span class="badge bg-light text-primary border fw-semibold text-capitalize" style="font-size: 0.65rem; letter-spacing: 0.5px;">${categoria}</span>
                                            </div>
                                            <h5 class="fw-bold text-dark m-0 text-truncate" style="font-size: 1rem; line-height: 1.2;">${titulo}</h5>
                                        </div>
                                    </div>

                                    <!-- Subtítulo o descripción corta -->
                                    ${subtitulo ? `<p class="text-muted m-0 text-truncate small" style="font-size: 0.85rem;">${subtitulo}</p>` : ''}

                                    <!-- Fila inferior: Fecha y enlace -->
                                    <div class="d-flex justify-content-between align-items-center border-top pt-1 mt-1 text-muted" style="font-size: 0.75rem;">
                                        <span class="text-primary fw-semibold">Ver detalle →</span>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="bi bi-calendar-event text-primary"></i>
                                            <span>${fecha}</span>
                                        </div>
                                    </div>
                                </div>`;
                        });

                        html += `</div></div>`;
                        box2.innerHTML = html;
                    })
                    .catch(err => {
                        console.error("Error al cargar novedades:", err);
                        box2.innerHTML = `<div class="p-4 w-100 text-danger text-center">Error al cargar las novedades.</div>`;
                    });
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

if (input) {
    input.addEventListener("input", function () {
        const text = this.value.trim();
        if (!text) {
            results.style.display = "none";
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
            toLocation.textContent = "Ingrese ciudad de destino";
            return;
        }
        const matches = Aeropuertos.buscar(text).filter(a => a.code !== input.value);
        renderOpcionesAeropuerto(toResults, matches, toInput, toLocation, toResults);
    });
}

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