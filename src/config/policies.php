<?php

/*
¿Puede el usuario actual editar esta aerolínea?
- admin: cualquiera
- ceo:   solo la suya
- cliente: no*/

function puedeEditarAerolinea(int $idAerolinea): bool {
    $user = usuarioActual();
    if (!$user) return false;

    if ($user['rol'] === 'admin') return true;

    if ($user['rol'] === 'ceo') {
        return isset($user['idAerolinea']) && (int)$user['idAerolinea'] === $idAerolinea;
    }

    return false;
}

/*
 ¿Puede el usuario actual ver/editar esta aerolínea?
 (para el detalle: el admin ve todas, el CEO solo la suya)
 */

function puedeVerAerolinea(int $idAerolinea): bool {
    return puedeEditarAerolinea($idAerolinea);
}

/**
 * ¿El usuario actual puede aprobar CEOs? (solo admin)
 */
function puedeAprobarCEO(): bool {
    return rolActual() === 'admin';
}

/**
 * ¿El usuario actual puede crear aerolíneas? (solo admin)
 */
function puedeCrearAerolinea(): bool {
    return rolActual() === 'admin';
}