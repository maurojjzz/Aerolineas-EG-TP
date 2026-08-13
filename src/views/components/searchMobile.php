<div class="container d-flex flex-column  gap-3 p-0">

    <div class="options-line container-fluid d-flex align-items-center bg-light rounded-4 shadow">
        
        <div class="active col option-line-item d-flex flex-column align-items-center pt-3 " data-option="vuelos" > 
            <img src="../../../public/img/icons/avion.png" alt="icono avion" class="iconos-search">
            <p>Vuelos</p>
        </div>

        <div class="col option-line-item d-flex flex-column align-items-center pt-3" data-option="promociones"> 
            <img src="../../../public/img/icons/promocion.png" alt="icono promocion" class="iconos-search">
            <p>Promociones</p>
        </div>

        <div class="col option-line-item  d-flex flex-column align-items-center pt-3" data-option="novedades" > 
            <img src="../../../public/img/icons/novedades.png" alt="icono megafono aludiendo a novedades" class="iconos-search">
            <p>Novedades</p>
        </div>

    </div>

    <div id="box2" class="container-fluid border rounded-3 shadow pb-3">

        <?php require './src/views/components/search/inputSearch.php' ?>

        <?php require './src/views/components/search/inputcalendar.php' ?>

        <div class="inputPasajeros d-flex align-items-center  w-100 mt-3 p-2 rounded-3 shadow-sm position-relative" >

            <label for="passengers" class="cal-label-pas position-absolute">Pasajeros</label>

            <img src="../../../public/img/icons/pasajeroicon.png" alt="icono pasajero" class="btn-icon-vuelos">

            <select name="passengers" class="form-select selectPasenger" aria-label="select pasajeros" id="passengers">
                <option selected value="1">1 Pasajero</option>
                <option value="2">2 Pasajeros</option>
                <option value="3">3 Pasajeros</option>
                <option value="4">4 Pasajeros</option>
                <option value="5">5 Pasajeros</option>
                <option value="6">6 Pasajeros</option>
                <option value="7+">+7 Pasajeros</option>
            </select>
            
        </div>

        <div class="d-flex justify-content-center mt-4">
            <button class="btn b-vuelos d-flex align-items-center justify-content-center gap-2 btn-primary w-100 btn-lg rounded-3 shadow-sm" id="search-button">
                <img src="../../../public/img/icons/lupa.png" alt="icono pasajero" class="autocomplete-icon">
                Buscar Vuelos
            </button>   
        </div>

    </div>


</div>