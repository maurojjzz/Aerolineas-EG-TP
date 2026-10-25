<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

$ctrl = new AerolineaController($link);

$id = (int)($_GET['id'] ?? 0);
$modoEditar = $id > 0;
$aero = null;

if ($modoEditar) {
    $aero = $ctrl->obtenerAerolineaPorId($id);
    if (!$aero) {
        flash_set('error', 'Aerolínea no encontrada.');
        redirect('index.php?pagina=aerolinea&seccion=listado');
    }
}

$paises = require __DIR__ . '/../../data/paises.php';

?>

<?php require __DIR__ . '/../components/alertToast.php';  ?>
<div class="" >
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <!-- <li class="breadcrumb-item"><a href="#">Inicio</a></li> -->
            <li class="breadcrumb-item"><a href="<?= url('/index.php?pagina=aerolinea&seccion=listado') ?>">Aerolínea</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $modoEditar ? 'Editar Aerolínea' : 'Crear Aerolínea' ?>
            </li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content  h-100">
        <h1 class="m-0 p-0 fs-2 fw-bold"><?= $modoEditar ? 'Editar Aerolínea' : 'Crear Aerolínea' ?></h1>
        <p class="m-0 p-0 mb-1 subt"><?= $modoEditar ? 'Modificá los datos de la aerolínea.' : 'Registra una nueva aerolínea en el sistema.' ?></p>
        
        <form 
            action="<?= $modoEditar ? url('src/routes/aerolinea.php?accion=editar&id=' . $id): url('src/routes/aerolinea.php?accion=crear')?>
" 
            method="POST" 
            class="row mt-2 m-0 g-0 p-0 gap-2 " 
            enctype="multipart/form-data"
            data-loading-form
        >
<p class="small text-secondary mb-2">Los campos con <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">asterisco</span> son obligatorios.</p>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <!-- seccion del formulario -->
            <div class="col-12 col-lg-9 border shadow rounded-2 py-4">

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 ">
                        <label class="form-label-t" for="nombre">Nombre de la aerolínea: <span class="text-danger" aria-hidden="true">*</span></label>
                        <input 
                            type="text" 
                            name="nombre" 
                            id="nombre" 
                            autocomplete="off" 
                            class="form-control ctm-inp" 
                            required 
                            placeholder="Aeroflux" 
                            minlength="3" 
                            maxlength="50"
                            value="<?= $modoEditar ? htmlspecialchars($aero['nombreAerolinea']) : '' ?>"
                        >
                        <p id="infoNombre" class="form-text-info">Nombre con el que operará comercialmente.</p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 ">
                        <label class="form-label-t" for="codigo">Codigo: <span class="text-danger" aria-hidden="true">*</span></label>
                        <input 
                            type="text" 
                            name="codigo" 
                            id="codigo" 
                            autocomplete="off" 
                            class="form-control ctm-inp" 
                            required 
                            placeholder="AFX" 
                            minlength="3" 
                            maxlength="5"
                            value="<?= $modoEditar ? htmlspecialchars($aero['codigoIATA']) : '' ?>"
                        >
                        <p id="infoCodigo" class="form-text-info">Codigo unico de la aerolínea.</p>
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    
                    <div class="pais-autocomplete position-relative col-sm-5 col-11">
                        <label class="form-label-t " for="pais">Pais: <span class="text-danger" aria-hidden="true">*</span></label>
                        <input
                            type="text"
                            id="pais"
                            class="form-control ctm-inp my-1"
                            placeholder="Seleccione un país"
                            autocomplete="off"
                            required
                            value="<?= $modoEditar ? htmlspecialchars($aero['codPais']) : '' ?>"    
                        >

                        <input type="hidden" id="paisCodigo" name="pais" value="<?= $modoEditar ? htmlspecialchars($aero['codPais']) : '' ?>">

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

                        <p id="infoPais" class="form-text-info">Pais de origen de la aerolinea.</p>


                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 ">
                        <label class="form-label-t" for="email">Email: <span class="text-danger" aria-hidden="true">*</span></label>
                        <input 
                            type="email" 
                            name="email" 
                            autocomplete="off" 
                            id="email" 
                            class="form-control ctm-inp" 
                            required 
                            value="<?= $modoEditar ? htmlspecialchars($aero['email']) : '' ?>" 
                            placeholder="aerolinea@example.com"
                        >
                        <p id="infoEmail" class="form-text-info">Email de contacto de la aerolinea.</p>
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 px-2">
                    <div class="form-group d-flex flex-column gap-1 col-11 ">
                        <label class="form-label-t" for="descripcion">Descripcion:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" autocomplete="off" placeholder="Ingrese una descripción">
<?= $modoEditar ? htmlspecialchars($aero['descripcion']) : '' ?></textarea>
                        <p class="form-text-info"> Descripción de la aerolínea. </p>
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0 ">
                    <div class="form-group d-flex flex-column gap-1 col-sm-4 col-10 ">

                        <label class="form-label-t" for="logo">Logotipo:</label>

                        <div class="logoAerolinea rounded-2 d-flex align-items-center gap-2 p-2
                                <?= ($modoEditar && $aero['logoUrl']) ? 'justify-content-start' : 'justify-content-center' ?>"
                                id="logoPreview"
                                role="button"
                                tabindex="0"
                                aria-controls="logo"
                                aria-label="Seleccionar el logotipo de la aerolínea">

                                <?php if ($modoEditar && $aero['logoUrl']): ?>
                                    <img
                                        src="<?= htmlspecialchars($aero['logoUrl']) ?>"
                                        class="logo-preview-img img-fluid"
                                        alt="logo actual"
                                    >
                                    <div class="d-flex flex-column w-100">
                                        <p class="m-0 fw-semibold">Logo actual</p>
                                        <p class="m-0 form-text-info">Subí uno nuevo para reemplazarlo</p>
                                        <div class="d-flex justify-content-end align-items-center gap-1 p-0">
                                            <button
                                                type="button"
                                                id="cambiarLogo"
                                                class="p-0 bg-transparent border-0"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                data-bs-title="Cambiar logotipo"
                                                data-bs-trigger="hover"
                                                aria-label="Cambiar logotipo"
                                            >
                                                <img src="<?= url('public/img/icons/redo.png') ?>" class="img-fluid btn-mini" alt="">
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= url('public/img/icons/subir.png') ?>" alt="" class="upload-foto">
                                    <div class="d-flex flex-column justify-content-center m-0 p-0 tetxt">
                                        <p class="firstLogoText p-0 m-0">Añada un logo a su aerolinea</p>
                                        <p class="sndLogoText p-0 m-0">haz click para seleccionar</p>
                                    </div>
                                <?php endif; ?>

                            </div>

                        <input type="file" accept="image/png, image/jpeg, image/webp" name="logo" id="logo" class="visually-hidden" aria-describedby="infoLogo errorLogo" >
                        
                        <p id="infoLogo" class="form-text-info">Formatos: JPG, PNG, WEBP. Máximo 2 MB.</p>
                        <p id="errorLogo" class="text-danger small m-0" role="alert"></p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-sm-6 col-11 ">
                        
                        <label class="form-label-t" for="estadoAerolinea">Estado:</label>

                        <select name="estadoAerolinea" id="estadoAerolinea" class="form-select">
                            <option value="activa" <?= (!$modoEditar || $aero['activo']) ? 'selected' : '' ?>>Activa</option>
                            <option value="inactiva" <?= ($modoEditar && !$aero['activo']) ? 'selected' : '' ?>>Inactiva</option>
                        </select>

                        <p class="form-text-info">Define si la aerolinea estara activa en el sistema</p> 
                    </div>
                </div>

                <div class="row d-flex justify-content-evenly m-0 g-0 p-0 ">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end col-11">
                        <a href="<?= url('index.php?pagina=aerolinea&seccion=listado') ?>" class="btn btn-outline-danger me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= $modoEditar ? 'Guardar cambios' : 'Crear Aerolínea' ?>
                        </button>
                    </div>
                </div>


            </div>

            <!-- seccion de validaciones -->
            <div class="d-none d-lg-flex col-lg flex-column align-items-center gap-4 ">

                <div class="d-flex flex-column border border-2 border-danger-subtle w-100 rounded-3 shadow d-none" id="erroresBox" role="alert" aria-live="assertive">
                    <div class="tituloBoxErrores bg-danger-subtle border-bottom d-flex flex-column align-items-center justify-content-center ">
                        <img src="<?= url('public/img/icons/alerta.png') ?>" alt="" class="img-fluid my-2" style="width: 28px; height: 28px;">
                        <h2 class="h5 text-danger">Validación del Formulario</h2>
                        <p id="cantidadErrores">Errores pendientes: 2</p>
                    </div>
                    <div class="errorBoxContent py-3 pe-3" >
                        <ul id="errorList">
                        </ul>
                    </div>
                </div>
                
                <div class="d-flex flex-column border border-2 border-primary-subtle w-100 rounded-3 shadow " id="recomendacionesBox">
                    <div class="tituloBoxErrores bg-primary-subtle border-bottom d-flex flex-column align-items-center justify-content-center ">
                        <img src="<?= url('public/img/icons/luz.png') ?>" alt="" class="img-fluid my-2" style="width: 28px; height: 28px;">
                        <h2 class="h5 text-primary">Recomendaciones</h2>
                        <p class="text-break text-center px-2">Tené en cuenta estos puntos antes de crear la aerolinea</p>
                    </div>

                    <div class="recomendaBoxContent d-flex flex-column align-items-center py-3 ps-2">

                        <div class="d-flex gap-2 mb-3  w-100 justify-content-center">
                            <div>
                                <p class="m-0 fw-semibold text-center">Código único</p>
                                <p class="m-0 form-text-info text-center">
                                    Utilizá un código corto que identifique a la aerolínea.
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mb-3 w-100 justify-content-center">
                            <div>
                                <p class="m-0 fw-semibold text-center">Nombre comercial</p>
                                <p class="m-0 form-text-info text-center">
                                    Ingresá el nombre con el que operará públicamente.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-3 w-100 justify-content-center ">
                            <div>
                                <p class="m-0 fw-semibold text-center">Información precisa</p>
                                <p class="m-0 form-text-info text-center">
                                    Verificá que el país y el email sean correctos.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex gap-2 w-100 justify-content-center">
                            <div>
                                <p class="m-0 fw-semibold text-center">Logotipo</p>
                                <p class="m-0 form-text-info text-center">
                                    Es opcional. JPG, PNG o WEBP de hasta 2 MB.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            

            

        </form>
    </div>


</div>