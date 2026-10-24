<?php
/**
 * Datos y helpers compartidos para las cards de Promociones y Novedades
 * del home (tabs del buscador y sección de destacados).
 *
 * Las consultas se cachean por request para no repetirlas.
 */

if (!function_exists('homeTabsCardsData')) {
    function homeTabsCardsData(int $limite = 6): array
    {
        static $cache = [];

        if (isset($cache[$limite])) {
            return $cache[$limite];
        }

        global $link;

        require_once __DIR__ . '/../../config/conexion.php';

        // Algunos layouts recorrían los links del menú con una variable llamada
        // $link y pisaban la conexión. Si eso pasa, reconectamos.
        if (!($link instanceof mysqli)) {
            require __DIR__ . '/../../config/conexion.php';
        }

        require_once __DIR__ . '/../../controllers/PromocionController.php';
        require_once __DIR__ . '/../../controllers/NovedadController.php';

        $promociones = [];
        $novedades   = [];

        try {
            $promoCtrl   = new PromocionController($link);
            $promociones = $promoCtrl->obtenerUltimasPublicadas($limite);
        } catch (Throwable $e) {
            $promociones = [];
        }

        try {
            $novedadCtrl = new NovedadController($link);
            $novedades   = $novedadCtrl->obtenerUltimasPublicadas($limite);
        } catch (Throwable $e) {
            $novedades = [];
        }

        return $cache[$limite] = [
            'promociones' => $promociones,
            'novedades'   => $novedades,
        ];
    }
}

if (!function_exists('homeTabsCardImagen')) {
    /**
     * Normaliza la ruta de la imagen de una novedad.
     * Soporta rutas locales (/public/img/...) y URLs absolutas.
     */
    function homeTabsCardImagen(?string $ruta): string
    {
        $ruta = trim((string)$ruta);

        if ($ruta === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $ruta) || str_starts_with($ruta, 'data:')) {
            return $ruta;
        }

        return url(ltrim($ruta, '/'));
    }
}

if (!function_exists('homeTabsCardCategoria')) {
    /**
     * Devuelve [clase del badge, ícono] para la categoría de una novedad.
     */
    function homeTabsCardCategoria(string $categoria): array
    {
        return match (strtolower($categoria)) {
            'vuelo'         => ['text-bg-primary', 'bi-airplane-fill'],
            'promocion'     => ['text-bg-info', 'bi-tag-fill'],
            'mantenimiento' => ['bg-warning text-dark', 'bi-tools'],
            'importante'    => ['text-bg-danger', 'bi-exclamation-triangle-fill'],
            default         => ['text-bg-success', 'bi-megaphone-fill'],
        };
    }
}

if (!function_exists('homeTabsCardFecha')) {
    function homeTabsCardFecha(?string $fecha, string $formato = 'd/m/Y'): string
    {
        if ($fecha === null || $fecha === '') {
            return '—';
        }

        $timestamp = strtotime($fecha);

        return $timestamp !== false ? date($formato, $timestamp) : '—';
    }
}
