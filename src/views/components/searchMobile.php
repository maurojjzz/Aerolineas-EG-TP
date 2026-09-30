<?php $paginaActual = $_GET['pagina'] ?? 'inicio';?>

<div class="container d-flex flex-column  gap-3 p-0">

    <div class="options-line container-fluid d-flex align-items-center bg-light rounded-4 shadow">
        
         <?php if ($paginaActual === 'inicio'): ?>

            <div class="active col option-line-item d-flex flex-column align-items-center pt-3" data-option="vuelos">
                <img src="<?= url('public/img/icons/avion.png') ?>" alt="icono avion" class="iconos-search">
                <p>Vuelos</p>
            </div>

            <div class="col option-line-item d-flex flex-column align-items-center pt-3" data-option="promociones">
                <img src="<?= url('public/img/icons/promocion.png') ?>" alt="icono promocion" class="iconos-search">
                <p>Promociones</p>
            </div>

            <div class="col option-line-item d-flex flex-column align-items-center pt-3" data-option="novedades">
                <img src="<?= url('public/img/icons/novedades.png') ?>" alt="icono megafono aludiendo a novedades" class="iconos-search">
                <p>Novedades</p>
            </div>

        <?php elseif (rolActual() === 'cliente' && $paginaActual === 'cliente'): ?>

            <div class="d-flex w-100 flex-row justify-content-start align-items-center gap-3 py-2">
                <img class="img-fluid ms-3 d-none d-sm-block"
                     src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                     alt="Icono avion titulo"
                     style="width: 42px; height: 42px;">
                <div class="d-flex flex-column justify-content-center align-items-start ms-2">
                    <span class="fw-semibold fs-5 p-0 m-0" style="color:#137AFF;">Buscar vuelos</span>
                    <span class="text-muted" style="font-size: 14px;">Encuentra las mejores opciones para tu próximo viaje</span>
                </div>
            </div>

        <?php endif; ?>


    </div>

    <div id="box2" class="container-fluid d-flex flex-column flex-lg-row justify-content-lg-center rounded-3 gap-lg-3 shadow pb-3">

        <?php require './src/views/components/search/inputSearch.php' ?>

        <?php require './src/views/components/search/inputcalendar.php' ?>

        <div class="d-flex flex-column flex-lg-row gap-lg-3  ctm-box-pas-btn">
            <div class="inputPasajeros d-flex align-items-center  w-100 mt-3 p-2 rounded-3 shadow-sm position-relative" >

                <label for="passengers" class="cal-label-pas position-absolute">Pasajeros</label>

                <img src="<?= url('public/img/icons/pasajeroicon.png') ?>" alt="icono pasajero" class="btn-icon-vuelos">

                <select name="passengers" class="form-select selectPasenger" aria-label="select pasajeros" id="passengers">
                    <option selected value="1">1 Pasajero</option>
                    <option value="2">2 Pasajeros</option>
                    <option value="3">3 Pasajeros</option>
                    <option value="4">4 Pasajeros</option>
                    <option value="5">5 Pasajeros</option>
                </select>
                
            </div>

            <div class="d-flex justify-content-center mt-4">
                <button class="btn b-vuelos d-flex align-items-center justify-content-center gap-2 btn-primary btn-lg rounded-3  shadow-sm" id="search-button">
                    <img src="<?= url('public/img/icons/lupa.png') ?>" alt="icono pasajero" class="autocomplete-icon">
                    <span class="d-lg-none d-xl-block">Buscar Vuelos</span>
                </button>   
            </div>
        </div>

    </div>


</div>