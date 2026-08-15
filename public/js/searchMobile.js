const options = document.querySelectorAll(".option-line-item");
const box2 = document.getElementById("box2");

const copiaVuelosMob= box2.cloneNode(true);

options.forEach((option) => {
    option.addEventListener("click", () => {
        options.forEach((item) => {
            item.classList.remove("active");
        });

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

const airports = [
    {
        code: "MAD",
        city: "Madrid",
        country: "España",
    },
    {
        code: "ASU",
        city: "Asunción",
        country: "Paraguay",
    },
    {
        code: "PTY",
        city: "Ciudad de Panamá",
        country: "Panamá",
    },
    {
        code: "ROS",
        city: "Rosario",
        country: "Argentina",
    },
    {
        code: "EZE",
        city: "Buenos Aires",
        country: "Argentina",
    },
    {
        code: "BCN",
        city: "Barcelona",
        country: "España",
    },
];

const input = document.getElementById("from");
const results = document.getElementById("from-results");
const fromLocation = document.getElementById("from-location");

const toInput = document.getElementById("to");
const toResults = document.getElementById("to-results");
const toLocation = document.getElementById("to-location");

input.addEventListener("input", function () {
    const text = this.value.toLowerCase().trim();

    results.innerHTML = "";

    if (text === "") {
        results.style.display = "none";
        fromLocation.textContent = "Ingrese ciudad de salida";

        return;
    }

    const matches = airports.filter(
        (airport) =>
            airport.code.toLowerCase().includes(text) ||
            airport.city.toLowerCase().includes(text) ||
            airport.country.toLowerCase().includes(text),
    );

    matches.forEach((airport) => {
        const option = document.createElement("div");

        option.classList.add("autocomplete-option");

        option.innerHTML = `
            <strong>${airport.code}</strong>
            <span>${airport.city}, ${airport.country}</span>
        `;

        option.addEventListener("click", function () {
            input.value = airport.code;

            fromLocation.textContent = `${airport.city}, ${airport.country}`;

            results.style.display = "none";
        });

        results.appendChild(option);
    });

    results.style.display = matches.length ? "block" : "none";
});

toInput.addEventListener("input", function () {
    const text = this.value.toLowerCase().trim();

    toResults.innerHTML = "";

    if (text === "") {
        toResults.style.display = "none";
        toLocation.textContent = "Ingrese ciudad de destino";

        return;
    }

    const matches = airports.filter(
        (airport) =>
            airport.code.toLowerCase().includes(text) ||
            airport.city.toLowerCase().includes(text) ||
            airport.country.toLowerCase().includes(text),
    );

    matches.forEach((airport) => {
        if (airport.code === input.value) {
            return;
        }

        const option = document.createElement("div");

        option.classList.add("autocomplete-option");

        option.innerHTML = `
            <strong>${airport.code}</strong>
            <span>${airport.city}, ${airport.country}</span>
        `;

        option.addEventListener("click", function () {
            toInput.value = airport.code;

            toLocation.textContent = `${airport.city}, ${airport.country}`;

            toResults.style.display = "none";
        });

        toResults.appendChild(option);
    });

    toResults.style.display = toResults.children.length ? "block" : "none";
});

const fechaInput = document.getElementById("fecha");
const fechaFormateada = document.getElementById("fecha-sub");

const fechaVueltaInput = document.getElementById("fecha-vuelta");
const fechaVueltaFormateada = document.getElementById("fecha-vuelta-sub");

const calendarVuelta = document.getElementById("calendar-vuelta");

let vueltaBloqueada = true;

const meses = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
];

fechaInput.addEventListener("input", function () {
    if (this.value === "") {
        fechaFormateada.textContent = "";

        vueltaBloqueada = true;

        calendarVuelta.classList.add("inputCalendar-bloqueado");

        fechaVueltaInput.value = "";
        fechaVueltaFormateada.textContent = "";

        return;
    }

    const partesFecha = this.value.split("-");

    const año = partesFecha[0];
    const mes = partesFecha[1];
    const dia = partesFecha[2];

    const mesTexto = meses[parseInt(mes) - 1];

    fechaFormateada.textContent = `${parseInt(dia)} ${mesTexto}, ${año}`;

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
    if (vueltaBloqueada) {
        event.preventDefault();
    }
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

    const partesFecha = this.value.split("-");

    const año = partesFecha[0];
    const mes = partesFecha[1];
    const dia = partesFecha[2];

    const mesTexto = meses[parseInt(mes) - 1];

    fechaVueltaFormateada.textContent = `${parseInt(dia)} ${mesTexto}, ${año}`;
});
