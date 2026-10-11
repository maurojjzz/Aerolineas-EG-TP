<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/NovedadController.php';

$ctrl = new NovedadController($link);

$id = $_GET['id'] ?? null;
$novedad = $id ? $ctrl->obtenerPorId($id) : null;
$esEdicion = !empty($novedad);
?>

<div class="contForm col-12 d-flex flex-column p-3 admin-content h-100">
    <h2 class="m-0 p-0 fs-2 fw-bold"><?= $esEdicion ? 'Editar Novedad' : 'Nueva Novedad' ?></h2>
    <p class="m-0 p-0 mb-3 subt"><?= $esEdicion ? 'Actualizá los datos de la novedad publicada.' : 'Creá un nuevo aviso o novedad para los usuarios/pasajeros.' ?></p>

    <form id="formNovedad" method="POST" action="<?= url('src/routes/novedades.php?accion=' . ($esEdicion ? 'editar' : 'crear')) ?>" novalidate class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <?php if ($esEdicion): ?>
            <input type="hidden" name="idNovedad" value="<?= $novedad['idNovedad'] ?>">
        <?php endif; ?>

        <div class="col-md-8">
            <div class="mb-3">
                <label for="titulo" class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                <input type="text" class="form-control ctm-inp" id="titulo" name="titulo" value="<?= htmlspecialchars($novedad['titulo'] ?? '') ?>" required minlength="3" maxlength="150">
                <div id="infoTitulo" class="form-text"></div>
            </div>

            <div class="mb-3">
                <label for="subtitulo" class="form-label fw-semibold">Subtítulo / Bajada</label>
                <input type="text" class="form-control ctm-inp" id="subtitulo" name="subtitulo" value="<?= htmlspecialchars($novedad['subtitulo'] ?? '') ?>" maxlength="255">
            </div>

            <div class="mb-3">
                <label for="contenido" class="form-label fw-semibold">Contenido <span class="text-danger">*</span></label>
                <textarea class="form-control ctm-inp" id="contenido" name="contenido" rows="6" required><?= htmlspecialchars($novedad['contenido'] ?? '') ?></textarea>
                <div id="infoContenido" class="form-text"></div>
            </div>

            <!-- Selector visual de las 8 imágenes predeterminadas (nove1 a nove8) -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Imagen ilustrativa (Seleccioná una)</label>
                <input type="hidden" id="imagen" name="imagen" value="<?= htmlspecialchars($novedad['imagen'] ?? '') ?>">
                
                <div class="row g-2">
                    <?php for ($i = 1; $i <= 8; $i++): 
                        // Cambiá la extensión si tus archivos son .jpg o .png (ej: .jpg)
                        $nombreArchivo = 'nove' . $i . '.webp'; 
                        $rutaImagen = url('public/img/' . $nombreArchivo);
                        $isSelected = (($novedad['imagen'] ?? '') === $rutaImagen);
                    ?>
                        <div class="col-6 col-sm-3">
                            <div class="card h-100 border preset-img-card <?= $isSelected ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;" onclick="seleccionarPreset('<?= $rutaImagen ?>', this)">
                                <img src="<?= $rutaImagen ?>" class="card-img-top rounded-top" alt="Nove <?= $i ?>" style="height: 70px; object-fit: cover;">
                                <div class="card-body p-1 text-center">
                                    <small class="text-muted" style="font-size: 0.75rem;">Imagen <?= $i ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="bg-light p-3 rounded border d-flex flex-column gap-3">
                <div>
                    <label for="categoria" class="form-label fw-semibold">Categoría</label>
                    <select name="categoria" id="categoria" class="form-select ctm-inp">
                        <option value="general" <?= ($novedad['categoria'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                        <option value="promocion" <?= ($novedad['categoria'] ?? '') === 'promocion' ? 'selected' : '' ?>>Promoción</option>
                        <option value="vuelo" <?= ($novedad['categoria'] ?? '') === 'vuelo' ? 'selected' : '' ?>>Vuelo / Operaciones</option>
                        <option value="importante" <?= ($novedad['categoria'] ?? '') === 'importante' ? 'selected' : '' ?>>Importante</option>
                    </select>
                </div>

                <div>
                    <label for="prioridad" class="form-label fw-semibold">Prioridad</label>
                    <select name="prioridad" id="prioridad" class="form-select ctm-inp">
                        <option value="baja" <?= ($novedad['prioridad'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                        <option value="media" <?= ($novedad['prioridad'] ?? 'media') === 'media' ? 'selected' : '' ?>>Media</option>
                        <option value="alta" <?= ($novedad['prioridad'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                    </select>
                </div>

                <div>
                    <label for="estado" class="form-label fw-semibold">Estado de Publicación</label>
                    <select name="estado" id="estado" class="form-select ctm-inp">
                        <option value="publicada" <?= ($novedad['estado'] ?? 'publicada') === 'publicada' ? 'selected' : '' ?>>Publicada</option>
                        <option value="borrador" <?= ($novedad['estado'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                        <option value="archivada" <?= ($novedad['estado'] ?? '') === 'archivada' ? 'selected' : '' ?>>Archivada</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="<?= url('index.php?pagina=novedades&seccion=listado') ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= $esEdicion ? 'Guardar Cambios' : 'Publicar Novedad' ?></button>
                </div>
            </div>
        </div>
    </form>
    
    <script>
    function seleccionarPreset(urlImagen, cardElement) {
        document.getElementById('imagen').value = urlImagen;
        document.querySelectorAll('.preset-img-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
        cardElement.classList.add('border-primary', 'bg-light');
    }
    </script>
    <script src="<?= url('public/js/admin/validacionNovedad.js') ?>"></script>
</div>