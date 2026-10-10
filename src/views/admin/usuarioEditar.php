<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

$ctrl = new UsuarioController($link);

$id = (int)($_GET['id'] ?? 0);
$usuario = $ctrl->obtenerUsuarioPorId($id);

if (!$usuario) {
    flash_set('error', 'Usuario no encontrado.');
    redirect('index.php?pagina=usuario&seccion=listado');
}

// Obtener listado de aerolíneas incluyendo la actual del usuario si estuviera inactiva
$idAeroActual = (int)($usuario['idAerolinea'] ?? 0);
$sqlAeros = "SELECT idAerolinea, nombreAerolinea 
             FROM aerolinea 
             WHERE activo = 1 OR idAerolinea = {$idAeroActual} 
             ORDER BY nombreAerolinea ASC";
$resAeros = mysqli_query($link, $sqlAeros);
$aerolineas = [];
while ($row = mysqli_fetch_assoc($resAeros)) {
    $aerolineas[] = $row;
}

$tiposDoc = ['DNI', 'Pasaporte', 'LC', 'LE'];
$rolActual = strtolower(trim($usuario['rol'] ?? ''));
$esCeo = ($rolActual === 'ceo');
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/index.php?pagina=usuario&seccion=listado') ?>">Usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Usuario</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h2 class="m-0 p-0 fs-2 fw-bold">Editar Usuario</h2>
        <p class="m-0 p-0 mb-1 subt">Modificá los datos del usuario seleccionado.</p>
        
        <form 
            action="<?= url('src/routes/usuarios.php?accion=editar&id=' . $id) ?>" 
            method="POST" 
            class="row mt-2 m-0 g-0 p-0 gap-2"
        >
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="col-12 col-lg-9 border shadow rounded-2 py-4">

                <!-- Fila 1: Nombre y Apellido -->
                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3">
                        <label class="form-label-t" for="nombre">Nombre:</label>
                        <input 
                            type="text" 
                            name="nombre" 
                            id="nombre" 
                            autocomplete="off" 
                            class="form-control ctm-inp" 
                            required 
                            placeholder="Ej. Juan" 
                            minlength="2" 
                            maxlength="50"
                            value="<?= htmlspecialchars($usuario['nombre']) ?>"
                        >
                        <p id="infoNombre" class="form-text-info">Nombre del usuario.</p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3">
                        <label class="form-label-t" for="apellido">Apellido:</label>
                        <input 
                            type="text" 
                            name="apellido" 
                            id="apellido" 
                            autocomplete="off" 
                            class="form-control ctm-inp" 
                            required 
                            placeholder="Ej. Pérez" 
                            minlength="2" 
                            maxlength="50"
                            value="<?= htmlspecialchars($usuario['apellido']) ?>"
                        >
                        <p id="infoApellido" class="form-text-info">Apellido del usuario.</p>
                    </div>
                </div>

                <!-- Fila 2: Tipo Documento y Número Documento -->
                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3">
                        <label class="form-label-t" for="tipoDocumento">Tipo de Documento:</label>
                        <select name="tipoDocumento" id="tipoDocumento" class="form-select ctm-inp" required>
                            <?php foreach ($tiposDoc as $td): ?>
                                <option value="<?= $td ?>" <?= (strcasecmp(trim($usuario['tipoDocumento'] ?? ''), $td) === 0) ? 'selected' : '' ?>>
                                    <?= $td ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p id="infoTipoDoc" class="form-text-info">Seleccione el documento de identidad.</p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3">
                        <label class="form-label-t" for="nroDocumento">Número de Documento:</label>
                        <input 
                            type="text" 
                            name="nroDocumento" 
                            id="nroDocumento" 
                            autocomplete="off" 
                            class="form-control ctm-inp" 
                            required 
                            placeholder="Ej. 38123456" 
                            minlength="6" 
                            maxlength="15"
                            value="<?= htmlspecialchars($usuario['nroDocumento']) ?>"
                        >
                        <p id="infoNroDoc" class="form-text-info">Documento único en la plataforma.</p>
                    </div>
                </div>

                <!-- Fila 3: Email y Teléfono -->
                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3">
                        <label class="form-label-t" for="email">Email:</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            autocomplete="off" 
                            class="form-control ctm-inp" 
                            required 
                            placeholder="usuario@ejemplo.com"
                            value="<?= htmlspecialchars($usuario['email']) ?>"
                        >
                        <p id="infoEmail" class="form-text-info">Correo institucional o personal.</p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3">
                        <label class="form-label-t" for="telefono">Teléfono:</label>
                        <input 
                            type="text" 
                            name="telefono" 
                            id="telefono" 
                            autocomplete="off" 
                            class="form-control ctm-inp" 
                            placeholder="Ej. +54 341 1234567"
                            value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"
                        >
                        <p id="infoTelefono" class="form-text-info">Número telefónico de contacto (opcional).</p>
                    </div>
                </div>

                <!-- Fila 4: Rol y Aerolínea Asociada (sólo CEO) -->
                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3">
                        <label class="form-label-t" for="selectRol">Rol de Usuario:</label>
                        <select name="rol" id="selectRol" class="form-select ctm-inp" required>
                            <option value="cliente" <?= $rolActual === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                            <option value="ceo" <?= $rolActual === 'ceo' ? 'selected' : '' ?>>CEO Aerolínea</option>
                            <option value="admin" <?= $rolActual === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                        <p id="infoRol" class="form-text-info">Nivel de permisos en la plataforma.</p>
                    </div>

                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11 mb-3 <?= $esCeo ? '' : 'd-none' ?>" id="grupoAerolinea">
                        <label class="form-label-t" for="idAerolinea">Aerolínea Asociada:</label>
                        <select name="idAerolinea" id="idAerolinea" class="form-select ctm-inp">
                            <option value="">-- Seleccionar Aerolínea --</option>
                            <?php foreach ($aerolineas as $aero): ?>
                                <option value="<?= $aero['idAerolinea'] ?>" <?= $usuario['idAerolinea'] == $aero['idAerolinea'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($aero['nombreAerolinea']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p id="infoAerolinea" class="form-text-info">Obligatorio únicamente para usuarios con rol CEO.</p>
                    </div>

                    <div class="col-sm-5 col-11 mb-3 <?= $esCeo ? 'd-none' : '' ?>" id="grupoSpacerAerolinea"></div>
                </div>

                <!-- Fila 5: Estado del Usuario -->
                <div class="row d-flex justify-content-evenly m-0 g-0 p-0 mb-3">
                    <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11">
                        <label class="form-label-t mb-1">Estado del Usuario:</label>
                        <div class="form-check form-switch fs-5 d-flex align-items-center gap-2 ps-0 mt-1">
                            <!-- Input hidden para asegurar envío de valor 0 al desmarcar -->
                            <input type="hidden" name="activo" value="0">
                            <input 
                                class="form-check-input ms-0 mt-0" 
                                type="checkbox" 
                                name="activo" 
                                value="1" 
                                id="switchActivo" 
                                <?= $usuario['activo'] ? 'checked' : '' ?>
                                style="cursor:pointer;"
                            >
                            <label class="form-check-label fs-6 fw-semibold <?= $usuario['activo'] ? 'text-success' : 'text-secondary' ?>" for="switchActivo" id="labelSwitchActivo" style="cursor:pointer;">
                                <?= $usuario['activo'] ? 'Cuenta Activa' : 'Cuenta Inactiva' ?>
                            </label>
                        </div>
                        <p class="form-text-info m-0 mt-1">Habilitá o deshabilitá el acceso del usuario al sistema.</p>
                    </div>

                    <div class="col-sm-5 col-11"></div>
                </div>

                <!-- Fila 6: Botones -->
                <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end col-11">
                        <a href="<?= url('index.php?pagina=usuario&seccion=listado') ?>" class="btn btn-outline-danger me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </div>

            </div>

            <div class="d-none d-lg-flex col-lg flex-column align-items-center gap-4">
                <div class="d-flex flex-column border border-2 border-primary-subtle w-100 rounded-3 shadow" id="recomendacionesBox">
                    <div class="tituloBoxErrores bg-primary-subtle border-bottom d-flex flex-column align-items-center justify-content-center">
                        <img src="<?= url('public/img/icons/luz.png') ?>" alt="icono recomendaciones" class="img-fluid my-2" style="width: 28px; height: 28px;">
                        <h5 class="text-primary">Recomendaciones</h5>
                        <p class="text-break text-center px-2">Tené en cuenta estos puntos antes de guardar los cambios</p>
                    </div>

                    <div class="recomendaBoxContent d-flex flex-column align-items-center py-3 ps-2">
                        <div class="d-flex gap-2 mb-3 w-100 justify-content-center">
                            <div>
                                <p class="m-0 fw-semibold text-center">Documentación única</p>
                                <p class="m-0 form-text-info text-center">Verificá que el número de documento no pertenezca a otro usuario.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mb-3 w-100 justify-content-center">
                            <div>
                                <p class="m-0 fw-semibold text-center">Correo institucional</p>
                                <p class="m-0 form-text-info text-center">Asegurate de que el email tenga un formato válido y accesible.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mb-3 w-100 justify-content-center">
                            <div>
                                <p class="m-0 fw-semibold text-center">Asignación de roles</p>
                                <p class="m-0 form-text-info text-center">Los usuarios con rol CEO deben asociarse obligatoriamente a una aerolínea.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectRol = document.getElementById("selectRol");
    const grupoAerolinea = document.getElementById("grupoAerolinea");
    const grupoSpacer = document.getElementById("grupoSpacerAerolinea");
    const switchActivo = document.getElementById("switchActivo");
    const labelSwitch = document.getElementById("labelSwitchActivo");

    selectRol.addEventListener("change", function () {
        if (this.value === "ceo") {
            grupoAerolinea.classList.remove("d-none");
            if (grupoSpacer) grupoSpacer.classList.add("d-none");
        } else {
            grupoAerolinea.classList.add("d-none");
            if (grupoSpacer) grupoSpacer.classList.remove("d-none");
        }
    });

    switchActivo.addEventListener("change", function () {
        if (this.checked) {
            labelSwitch.textContent = "Cuenta Activa";
            labelSwitch.className = "form-check-label fs-6 fw-semibold text-success";
        } else {
            labelSwitch.textContent = "Cuenta Inactiva";
            labelSwitch.className = "form-check-label fs-6 fw-semibold text-secondary";
        }
    });
});
</script>