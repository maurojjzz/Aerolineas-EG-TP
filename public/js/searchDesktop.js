Aeropuertos.cargar();

const tabs= document.querySelectorAll(".desktop-tab");
const boxContent = document.getElementsByClassName("desktop-search-form")[0];

const copiaVuelos = boxContent.cloneNode(true);

tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        tabs.forEach((item) => item.classList.remove("active"));
        tab.classList.add("active");

        const selected = tab.querySelector("span").textContent.toLowerCase();

        if (selected === "vuelos") {
            boxContent.innerHTML = copiaVuelos.innerHTML;
        }
        if (selected === "promociones") {
            boxContent.textContent = "Seleccionaste Promociones";
        }
        if (selected === "novedades") {
            boxContent.textContent = "Seleccionaste Novedades";
        }
    });
});

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

inputSalida.addEventListener("input", function () {
    const text = this.value.trim();

    if (!text) {
        inputSalidaResult.style.display = "none";
        inputSalidaLocation.textContent = "Ingrese salida";
        return;
    }

    renderOpcionesAeropuerto(
        inputSalidaResult,
        Aeropuertos.buscar(text),
        inputSalida,
        inputSalidaLocation,
        inputSalidaResult
    );
});

inputDestino.addEventListener("input", function () {
    const text = this.value.trim();

    if (!text) {
        inputDestinoResult.style.display = "none";
        inputDestinoLocation.textContent = "Ingrese destino";
        return;
    }

    const matches = Aeropuertos.buscar(text).filter(a => a.code !== inputSalida.value);

    renderOpcionesAeropuerto(
        inputDestinoResult,
        matches,
        inputDestino,
        inputDestinoLocation,
        inputDestinoResult
    );
});


const swapButton = document.getElementById("swap-desktop");

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

const mesesDesktop = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

const dateInput            = document.getElementById("fecha-desktop");
const dateInputWord        = document.getElementById("fecha-sub-desktop");
const dateInputReturn      = document.getElementById("fecha-vuelta-desktop");
const dateInputReturnWord  = document.getElementById("fecha-vuelta-sub-desktop");

dateInput.addEventListener("input", function () {
    if (this.value === "") {
        dateInputWord.textContent = "Seleccione fecha";
        dateInputReturn.min = "";
        return;
    }

    const [año, mes, dia] = this.value.split("-");
    dateInputWord.textContent = `${parseInt(dia)} ${mesesDesktop[parseInt(mes) - 1]}, ${año}`;
    dateInputReturn.min = this.value;

    if (dateInputReturn.value && dateInputReturn.value < this.value) {
        dateInputReturn.value = "";
        dateInputReturnWord.textContent = "Seleccione fecha";
    }
});

dateInputReturn.addEventListener("input", function () {
    if (this.value === "") {
        dateInputReturnWord.textContent = "";
        return;
    }

    const [año, mes, dia] = this.value.split("-");
    dateInputReturnWord.textContent = `${parseInt(dia)} ${mesesDesktop[parseInt(mes) - 1]}, ${año}`;
});