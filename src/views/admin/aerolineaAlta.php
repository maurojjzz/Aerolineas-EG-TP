<?php

$paises = [
    ["codigo" => "ARG", "nombre" => "Argentina"],
    ["codigo" => "BRA", "nombre" => "Brasil"],
    ["codigo" => "CHL", "nombre" => "Chile"],
    ["codigo" => "COL", "nombre" => "Colombia"],
    ["codigo" => "ECU", "nombre" => "Ecuador"],
    ["codigo" => "ESP", "nombre" => "España"],
    ["codigo" => "USA", "nombre" => "Estados Unidos"],
    ["codigo" => "MEX", "nombre" => "México"],
    ["codigo" => "PAN", "nombre" => "Panamá"],
    ["codigo" => "PRY", "nombre" => "Paraguay"],
    ["codigo" => "PER", "nombre" => "Perú"],
    ["codigo" => "URY", "nombre" => "Uruguay"],
    ["codigo" => "VEN", "nombre" => "Venezuela"]
];

?>

<div class="" >
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="#">Aerolínea</a></li>
            <li class="breadcrumb-item active" aria-current="page">Crear Aerolínea</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content  h-100">
        <h2 class="m-0 p-0 fs-2 fw-bold">Crear Aerolínea</h2>
        <p class="m-0 p-0 mb-1 subt">Registra una nueva aerolínea en el sistema.</p>
        
        <form action="#" method="POST" class="row m-0 g-0 p-0 gap-2 " enctype="multipart/form-data">

            <!-- seccion del formulario -->
            <div class="col-12 col-lg-9 border shadow rounded-2 py-4">

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    <div class="form-group d-flex flex-column gap-1 col-5 ">
                        <label class="form-label-t" for="nombre">Nombre de la aerolínea:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control ctm-inp" required placeholder="Aeroflux">
                        <p class="form-text-info">Nombre con el que operará comercialmente.</p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-5 ">
                        <label class="form-label-t" for="codigo">Codigo:</label>
                        <input type="text" name="codigo" id="codigo" class="form-control ctm-inp" required placeholder="AFX">
                        <p class="form-text-info">Nombre con el que operará comercialmente.</p>
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    
                    <div class="pais-autocomplete position-relative col-5">

                        <label class="form-label-t " for="pais">Pais:</label>

                        <input
                            type="text"
                            id="pais"
                            name="pais"
                            class="form-control ctm-inp my-1"
                            placeholder="Seleccione un país"
                            autocomplete="off"
                        >

                        <div id="paises-results" class="paises-results">

                            <?php foreach ($paises as $pais): ?>

                                <div
                                    class="pais-option"
                                    data-codigo="<?= $pais["codigo"] ?>"
                                    data-nombre="<?= $pais["nombre"] ?>"
                                >
                                    <strong><?= $pais["codigo"] ?></strong>
                                    <span><?= $pais["nombre"] ?></span>
                                </div>

                            <?php endforeach; ?>
                        </div>

                        <p class="form-text-info">Pais de origen de la aerolinea.</p>


                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-5 ">
                        <label class="form-label-t" for="email">Email:</label>
                        <input type="text" name="email" id="email" class="form-control ctm-inp" required placeholder="aerolinea@example.com">
                        <p class="form-text-info">Email de contacto de la aerolinea.</p>
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 px-2">
                    <div class="form-group d-flex flex-column gap-1 col-11 ">
                        <label class="form-label-t" for="descripcion">Descripcion:</label>
                        <textarea class="form-control" id="exampleFormControlTextarea1" name="descripcion" rows="3" required placeholder="Ingrese una descripción"></textarea>
                        <p class="form-text-info"> Descripción de la aerolínea. </p>
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0 ">
                    <div class="form-group d-flex flex-column gap-1 col-4 ">

                        <label class="form-label-t" for="logo">
                            <p class="form-label-t mb-1">Logotipo:</p> 
                            <div class="logoAerolinea rounded-2 d-flex align-items-center justify-content-center gap-2 p-2 " id="logoPreview">
                                <img src="../../../public/img/icons/subir.png" alt="icono subir imagen admin" class="upload-foto">
                                <div class="d-flex flex-column justify-content-center m-0 p-0 tetxt">
                                    <p class="firstLogoText p-0 m-0">Arrastra y suelta tu archivo aquí</p>
                                    <p class="sndLogoText p-0 m-0">o haz click para seleccionar</p>

                                </div>
                            </div>
                        
                        </label>

                        <input type="file" accept="image/png, image/jpeg, image/webp" name="logo" id="logo" class="d-none" required>
                        
                        <p class="form-text-info">Formatos: JPG, PNG, WEBP. Max: 2MB</p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-6 ">
                        
                        <label class="form-label-t" for="estadoAerolinea">Estado:</label>

                        <select name="estadoAerolinea" class="form-select">
                            <option value="activa" selected>Activa</option>
                            <option value="inactiva">Inactiva</option>
                        </select>

                        <p class="form-text-info">Define si la aerolinea estara activa en el sistema</p> 
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0 ">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end col-11">
                        <button type="button" class="btn btn-outline-danger me-md-2">Cancelar</button>
                        <button type="button" class="btn btn-primary">Crear Aerolinea</button>
                    </div>
                </div>


            </div>

            <!-- seccion de validaciones -->
            <div class="d-none d-lg-flex col-lg border shadow rounded-2 p-2">
                <div class="form-group d-flex flex-column gap-1">
                    <label for="validacion">Validación:</label>
                    <input type="text" name="validacion" id="validacion" class="form-control" disabled>
                </div>

            </div>
            

            

        </form>
    </div>


</div>