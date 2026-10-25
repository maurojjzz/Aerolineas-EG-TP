<div class="d-flex flex-wrap flex-lg-row flex-lg-nowrap justify-content-center align-items-center gap-3">
    <div class="autocomplete d-flex flex-row align-items-center px-2 gap-2 mt-3 p-2 rounded-3 shadow-sm">

        <img src="<?= url('public/img/icons/avion.png') ?>" alt="" class="autocomplete-icon">

        <div class="input-custom d-flex flex-column position-relative">

            <label class="label-search position-absolute" for="from">Salida</label>

            <input
                class="w-100 border-0 bg-transparent custom-input-b2"
                type="text"
                id="from"
                name="origen"
                placeholder="País o ciudad"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="from-results"
            >

            <p id="from-location" class="flight-location">
                Ingrese salida
            </p>
                    
            <span class="flight-arrow position-absolute">⌄</span>
        </div>
                

        <div id="from-results" class="autocomplete-results" role="listbox"></div>
    </div>

    <div class="autocomplete d-flex flex-row align-items-center px-2 gap-2 mt-3 p-2 rounded-3 shadow-sm">

        <img src="<?= url('public/img/icons/avion.png') ?>" alt="" class="autocomplete-icon">

        <div class="input-custom d-flex flex-column position-relative">

            <label class="label-search position-absolute" for="to">Destino</label>

            <input
                class="w-100 border-0 bg-transparent custom-input-b2"
                type="text"
                id="to"
                name="destino"
                placeholder="País o ciudad"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="to-results"
            >

            <p id="to-location" class="flight-location">
                Ingrese destino
            </p>
                    
            <span class="flight-arrow position-absolute">⌄</span>
        </div>
                

        <div id="to-results" class="autocomplete-results" role="listbox"></div>
    </div>
</div>