
<?php require __DIR__ . '/../components/alertToast.php';  ?>
<div class="" >
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="#">Aerolínea</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Aerolíneas</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content  h-100">
        <h2 class="m-0 p-0 fs-2 fw-bold">Gestion de Aerolíneas</h2>
        <p class="m-0 p-0 mb-1 subt ">Administra las aerolineas del sistema.</p>
        
        <div class="d-flex flex-column gap-2 p-2 contForm__cont">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="m-0 p-0 fs-4 fw-bold">Listado de Aerolíneas</h3>
                <a href="<?= url('index.php?pagina=aerolinea&seccion=alta') ?>" class="btn btn-primary">Agregar Aerolínea</a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">País</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="aerolineaTableBody">
                        <!-- Aquí se llenará la tabla con JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
