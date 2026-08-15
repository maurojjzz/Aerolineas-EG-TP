const tabs = document.querySelectorAll(".desktop-tab");
const boxContent = document.getElementsByClassName("desktop-search-form")[0];

const copiaVuelos = boxContent.cloneNode(true);

tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        tabs.forEach((item) => {
            item.classList.remove("active");
        });

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

const inputSalida = document.getElementById("from-desktop");
const inputSalidaResult = document.getElementById("from-results-desktop");
const inputSalidaLocation = document.getElementById("from-location-desktop");

const inputDestino = document.getElementById("to-desktop");
const inputDestinoResult = document.getElementById("to-results-desktop");
const inputDestinoLocation = document.getElementById("to-location-desktop");

inputSalida.addEventListener("input", function () {
    const testSal = this.value.toLowerCase().trim();

    inputSalidaResult.innerHTML = "";

    if (testSal === "") {
        inputSalidaResult.style.display = "none";
        inputSalidaLocation.textContent = "Ingrese Salida";
        return;
    }

    const matches = airports.filter(
        (airport) =>
            airport.code.toLowerCase().includes(testSal) ||
            airport.city.toLowerCase().includes(testSal) ||
            airport.country.toLowerCase().includes(testSal),
    );

    matches.forEach((airport) => {
        const option = document.createElement("div");

        option.classList.add("autocomplete-option");

        option.innerHTML = `
            <strong>${airport.code}</strong>
            <span>${airport.city}, ${airport.country}</span>
        `;

        option.addEventListener("click", function () {
            inputSalida.value = airport.code;

            inputSalidaLocation.textContent = `${airport.city}, ${airport.country}`;

            inputSalidaResult.style.display = "none";
        });

        inputSalidaResult.appendChild(option);
    });
    inputSalidaResult.style.display = matches.length ? "block" : "none";
});

inputDestino.addEventListener("input", function () {
    const testDes = this.value.toLowerCase().trim();

    inputDestinoResult.innerHTML = "";

    if (testDes === "") {
        inputDestinoResult.style.display = "none";
        inputDestinoLocation.textContent = "Ingrese Destino";
        return;
    }

    const matches = airports.filter(
        (airport) =>
            airport.code.toLowerCase().includes(testDes) ||
            airport.city.toLowerCase().includes(testDes) ||
            airport.country.toLowerCase().includes(testDes),
    );

    matches.forEach((airport) => {
        if (airport.code === inputSalida.value) {
            return;
        }

        const option = document.createElement("div");

        option.classList.add("autocomplete-option");

        option.innerHTML = `
                <strong>${airport.code}</strong>
                <span>${airport.city}, ${airport.country}</span>
            `;

        option.addEventListener("click", function () {
            inputDestino.value = airport.code;

            inputDestinoLocation.textContent = `${airport.city}, ${airport.country}`;

            inputDestinoResult.style.display = "none";
        });

        inputDestinoResult.appendChild(option);
    });

    inputDestinoResult.style.display = matches.length ? "block" : "none";
});

const dateInput = document.getElementById("fecha-desktop");
const dateInputWord = document.getElementById("fecha-sub-desktop");

const dateInputReturn = document.getElementById("fecha-vuelta-desktop");
const dateInputReturnWord = document.getElementById("fecha-vuelta-sub-desktop");


dateInput.addEventListener("input", function () {

    if (this.value === "") {
        dateInputWord.textContent = "Seleccione fecha";
        dateInputReturn.min = "";
        return;
    }

    const partesFecha = this.value.split("-");

    const año = partesFecha[0];
    const mes = partesFecha[1];
    const dia = partesFecha[2];

    const mesTexto = meses[parseInt(mes) - 1];

    dateInputWord.textContent = `${parseInt(dia)} ${mesTexto}, ${año}`;

    dateInputReturn.min = this.value;

    if (dateInputWord.value && dateInputReturn.value < this.value) {
        dateInputReturn.value = "";
        dateInputReturnWord.textContent = "Seleccione fecha";
    }

});


dateInputReturn.addEventListener("input", function () {

    if (this.value === "") {
        dateInputReturnWord.textContent = "";
        return;
    }

    const partesFecha = this.value.split("-");

    const año = partesFecha[0];
    const mes = partesFecha[1];
    const dia = partesFecha[2];

    const mesTexto = meses[parseInt(mes) - 1];

    dateInputReturnWord.textContent = `${parseInt(dia)} ${mesTexto}, ${año}`;
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
    inputSalidaResult.style.display = "none";
    inputDestinoResult.style.display = "none";
});