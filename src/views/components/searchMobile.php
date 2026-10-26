<?php
$paginaActual = $_GET['pagina'] ?? 'inicio';
$esCliente    = $paginaActual === 'cliente' && rolActual() === 'cliente';
$mostrarTabs  = $paginaActual === 'inicio' || $esCliente;
?>

<div class="container d-flex flex-column gap-3 p-0">

    <div class="options-line-card container-fluid bg-light rounded-4 shadow">

        <?php if ($esCliente): ?>
            <div class="options-line-header">
                <div class="d-flex w-100 flex-row justify-content-start align-items-center gap-3 py-2 px-2">
                    <img class="img-fluid d-none d-sm-block"
                        src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                        alt="Icono de avión"
                        style="width: 42px; height: 42px;">
                    <div class="d-flex flex-column justify-content-center align-items-start ms-2">
                        <span class="fw-semibold fs-5 p-0 m-0" style="color:#137AFF;">Buscar vuelos</span>
                        <span class="text-muted" style="font-size: 14px;">Encuentra las mejores opciones para tu próximo viaje</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($mostrarTabs): ?>
            <div class="options-line d-flex align-items-center" role="tablist" aria-label="Qué querés buscar">
                <button type="button" role="tab" aria-selected="true" aria-controls="mobilePanelVuelos"
                        id="mobileTabVuelos" class="active col option-line-item d-flex flex-column align-items-center pt-3 border-0 bg-transparent" data-option="vuelos">
                    <img src="<?= url('public/img/icons/avion.png') ?>" alt="Icono de avión" class="iconos-search">
                    <span>Vuelos</span>
                </button>

                <button type="button" role="tab" aria-selected="false" aria-controls="mobilePanelPromociones"
                        id="mobileTabPromociones" class="col option-line-item d-flex flex-column align-items-center pt-3 border-0 bg-transparent" data-option="promociones">
                    <img src="<?= url('public/img/icons/promocion.png') ?>" alt="Icono de promoción" class="iconos-search">
                    <span>Promociones</span>
                </button>

                <button type="button" role="tab" aria-selected="false" aria-controls="mobilePanelNovedades"
                        id="mobileTabNovedades" class="col option-line-item d-flex flex-column align-items-center pt-3 border-0 bg-transparent" data-option="novedades">
                    <img src="<?= url('public/img/icons/novedades.png') ?>" alt="Icono de novedades" class="iconos-search">
                    <span>Novedades</span>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- FORMULARIO MOBILE -->
    <div id="mobilePanelVuelos" role="tabpanel" class="w-100"
         <?= $mostrarTabs ? 'aria-labelledby="mobileTabVuelos"' : '' ?>>
    <form action="<?= url('index.php?pagina=buscar-vuelos') ?>" method="GET" id="formMobile" class="w-100">
        <input type="hidden" name="pagina" value="buscar-vuelos">
        <input type="hidden" name="tipo_viaje" id="mobileTipoViajeHidden" value="solo_ida">

        <div id="box2" class="container-fluid d-flex flex-column flex-lg-row justify-content-lg-center rounded-3 pt-2 gap-lg-3 shadow pb-3">

            <!-- RADIOS IDA / IDA Y VUELTA -->
            <fieldset class="border-0 p-0 m-0 px-3 pb-2 col-12">
                <legend class="visually-hidden">Tipo de viaje</legend>

                <div class="d-flex align-items-center gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_viaje_radio" id="mobileSoloIda" value="solo_ida" checked>
                        <label class="form-check-label fw-semibold" for="mobileSoloIda" style="font-size:0.9rem;">Solo ida</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_viaje_radio" id="mobileIdaVuelta" value="ida_vuelta">
                        <label class="form-check-label fw-semibold" for="mobileIdaVuelta" style="font-size:0.9rem;">Ida y vuelta</label>
                    </div>
                </div>
            </fieldset>

            <?php require './src/views/components/search/inputSearch.php' ?>

            <?php require './src/views/components/search/inputcalendar.php' ?>

            <div class="d-flex flex-column flex-lg-row gap-lg-3  ctm-box-pas-btn">
                <div class="inputPasajeros d-flex align-items-center  w-100 mt-3 p-2 rounded-3 shadow-sm position-relative" >

                    <label for="passengers" class="cal-label-pas position-absolute">Pasajeros</label>
                    <img src="<?= url('public/img/icons/pasajeroicon.png') ?>" alt="Icono de pasajero" class="btn-icon-vuelos">

                    <select name="pasajeros" class="form-select selectPasenger" id="passengers">
                        <option selected value="1">1 Pasajero</option>
                        <option value="2">2 Pasajeros</option>
                        <option value="3">3 Pasajeros</option>
                        <option value="4">4 Pasajeros</option>
                        <option value="5">5 Pasajeros</option>
                    </select>
                    
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <button 
                        type="submit" 
                        class="btn b-vuelos d-flex align-items-center justify-content-center gap-2 btn-primary btn-lg rounded-3  shadow-sm" 
                        id="search-button"
                        aria-label="Buscar vuelos"
                    >
                        <img src="<?= url('public/img/icons/lupa.png') ?>" alt="Icono de búsqueda" class="autocomplete-icon">
                        <span class="d-lg-none d-xl-block">Buscar Vuelos</span>
                    </button>   
                </div>
            </div>

        </div>
    </form>
    </div>

    <?php if ($mostrarTabs): ?>
        <div id="mobilePanelTabs" class="home-tabs-panels d-none">
            <?php $homeTabsPrefijo = 'mobile'; require __DIR__ . '/homeTabsCards.php'; ?>
        </div>
    <?php endif; ?>
</div>
