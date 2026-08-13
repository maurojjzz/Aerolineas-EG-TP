<div class="autocomplete d-flex flex-row align-items-center px-2 gap-2 mt-3 p-2 rounded-3 shadow-sm">

    <img src="../../../public/img/icons/avion.png" alt="icono avion barra busqueda" class="autocomplete-icon">

    <div class="input-custom d-flex flex-column position-relative">

        <label class="label-search position-absolute" for="from">Salida</label>

        <input
            class="w-100 border-0 bg-transparent custom-input-b2"
            type="text"
            id="from"
            placeholder="Pais o Ciudad"
            autocomplete="off"
        >

        <p id="from-location" class="flight-location">
            Ingrese lugar de salida
        </p>
                
        <span class="flight-arrow position-absolute">⌄</span>
    </div>
            

    <div id="from-results" class="autocomplete-results"></div>
</div>

<div class="autocomplete d-flex flex-row align-items-center px-2 gap-2 mt-3 p-2 rounded-3 shadow-sm">

    <img src="../../../public/img/icons/avion.png" alt="icono avion barra busqueda" class="autocomplete-icon">

    <div class="input-custom d-flex flex-column position-relative">

        <label class="label-search position-absolute" for="to">Destino</label>

        <input
            class="w-100 border-0 bg-transparent custom-input-b2"
            type="text"
            id="to"
            placeholder="Pais o Ciudad"
            autocomplete="off"
        >

        <p id="to-location" class="flight-location">
            Ingrese lugar de destino
        </p>
                
        <span class="flight-arrow position-absolute">⌄</span>
    </div>
            

    <div id="to-results" class="autocomplete-results"></div>
</div>