<div class="container-xxl">

    <div class="desktop-search bg-light rounded-4 shadow overflow-visible">

        <div class="desktop-search-tabs d-flex align-items-center">

            <button class="desktop-tab active d-flex align-items-center gap-2">
                <img src=" <?= url('public/img/icons/avion.png') ?>" alt="Icono avión">
                <span>Vuelos</span>
            </button>

            <button class="desktop-tab">
                <img src="<?= url('public/img/icons/promocion.png') ?>" alt="Icono promoción">
                <span>Promociones</span>
            </button>

            <button class="desktop-tab">
                <img src="<?= url('public/img/icons/novedades.png') ?>" alt="Icono novedades">
                <span>Novedades</span>
            </button>
        </div>


        <form class="desktop-search-form d-flex align-items-center ">

            <div class="desktop-field desktop-airport position-relative">

                <label for="from-desktop">Salida</label>

                <input
                    type="text"
                    id="from-desktop"
                    class="desktop-main-input"
                    placeholder="Ciudad"
                    autocomplete="off"
                >

                <p id="from-location-desktop">
                    Ingrese salida
                </p>

                <span class="desktop-arrow">⌄</span>

                <div
                    id="from-results-desktop"
                    class="autocomplete-results">
                </div>
            </div>


            <button type="button" class="swap-btn d-flex align-items-center justify-content-center" id="swap-desktop" aria-label="intercambiar origen y destino">
                ⇄
            </button>


            <div class="desktop-field desktop-airport position-relative">
                <label for="to-desktop">Destino</label>

                <input
                    type="text"
                    id="to-desktop"
                    class="desktop-main-input"
                    placeholder="Ciudad"
                    autocomplete="off"
                >

                <p id="to-location-desktop">
                    Ingrese destino
                </p>

                <span class="desktop-arrow">⌄</span>

                <div
                    id="to-results-desktop"
                    class="autocomplete-results">
                </div>

            </div>


            <div class="desktop-divider"></div>


            <div class="desktop-field desktop-date">

                <label for="fecha-desktop">
                    Salida
                </label>

                <input
                    type="date"
                    id="fecha-desktop"
                    class="desktop-date-input"
                    min="<?php echo date('Y-m-d'); ?>"
                >

                <p id="fecha-sub-desktop">
                    Seleccione fecha
                </p>

            </div>


            <div class="desktop-divider"></div>


            <div class="desktop-field desktop-date">

                <label for="fecha-vuelta-desktop">
                    Vuelta
                </label>

                <input
                    type="date"
                    id="fecha-vuelta-desktop"
                    class="desktop-date-input"
                    min="<?php echo date('Y-m-d'); ?>"
                >

                <p id="fecha-vuelta-sub-desktop">
                    Seleccione fecha
                </p>

            </div>


            <div class="desktop-divider"></div>


            <div class="desktop-field desktop-passengers">

                <label for="passengers-desktop">
                    Pasajeros
                </label>

                <select
                    id="passengers-desktop" class="form-select desktop-passenger-select">
                    <option value="1">1 Pasajero</option>
                    <option value="2">2 Pasajeros</option>
                    <option value="3">3 Pasajeros</option>
                    <option value="4">4 Pasajeros</option>
                    <option value="5">5 Pasajeros</option>

                </select>


            </div>


            <button type="submit" class="btn desktop-search-btn ms-auto gap-2">
                <img src="<?= url('public/img/icons/lupa.png') ?>" alt="icono pasajero" class="autocomplete-icon">
                Buscar Vuelos
            </button>

        </form>

    </div>

</div>