<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glosario — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body>

    <!-- Header unificado -->
    <?php require __DIR__ . '/layouts/header.php'; ?>

    <main id="contenido" tabindex="-1" class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                
                <!-- Tarjeta contenedora -->
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white">
                    
                    <div class="mb-4 pb-3 border-bottom">
                        <h1 class="fw-bold text-dark display-6 mb-2">Glosario</h1>
                        <p class="text-secondary m-0">Términos técnicos de la industria aeronáutica explicados de forma sencilla.</p>
                    </div>

                    <!-- Buscador -->
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="buscadorGlosario" 
                                   placeholder="Buscar término..." 
                                   aria-label="Buscar término en el glosario">
                        </div>
                    </div>

                    <div class="text-secondary" id="glosarioContainer">
                        
                        <!-- A -->
                        <h2 class="h5 fw-bold text-dark mt-4">A</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Aerolínea</h3>
                        <p class="mb-3">Empresa dedicada al transporte de pasajeros y carga por vía aérea. En Volaré, trabaja con múltiples aerolíneas asociadas que operan los vuelos disponibles.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Aeropuerto</h3>
                        <p class="mb-3">Lugar acondicionado para el aterrizaje, despegue y movimiento de aeronaves. También cuenta con instalaciones para el embarque y desembarque de pasajeros.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Asiento</h3>
                        <p class="mb-3">Lugar individual designado para cada pasajero dentro de la aeronave. Puede seleccionarse al momento de la reserva, sujeto a disponibilidad.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Check-in</h3>
                        <p class="mb-3">Procedimiento de registro del pasajero antes del vuelo. Incluye la verificación de documentos, asignación de asiento y emisión de la tarjeta de embarque. Puede realizarse online o en el aeropuerto.</p>

                        <!-- B -->
                        <h2 class="h5 fw-bold text-dark mt-4">B</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Boarding Pass</h3>
                        <p class="mb-3">Tarjeta de embarque. Documento que acredita al pasajero para abordar el avión. Contiene información como el número de vuelo, asiento, puerta de embarque y hora de salida.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Baggage / Equipaje</h3>
                        <p class="mb-3">Pertenencias que el pasajero transporta. Se divide en equipaje de mano (llevado en cabina) y equipaje facturado (transportado en bodega). La cantidad y peso dependen de la tarifa adquirida.</p>

                        <!-- C -->
                        <h2 class="h5 fw-bold text-dark mt-4">C</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Cancelación</h3>
                        <p class="mb-3">Anulación de una reserva de vuelo por parte del pasajero. Puede tener costos asociados dependiendo de la tarifa y el tiempo de anticipación con respecto a la fecha del vuelo.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Código IATA</h3>
                        <p class="mb-3">Código de tres letras asignado por la Asociación de Transporte Aéreo Internacional para identificar aerolíneas, aeropuertos y ciudades. Ejemplo: EZE para Aeropuerto Internacional de Ezeiza.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Conexión / Escala</h3>
                        <p class="mb-3">Parada intermedia en un vuelo donde el pasajero debe cambiar de avión para continuar su viaje. El tiempo mínimo de conexión varía según el aeropuerto y la aerolínea.</p>

                        <!-- D -->
                        <h2 class="h5 fw-bold text-dark mt-4">D</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Demora</h3>
                        <p class="mb-3">Retraso en la hora de salida programada de un vuelo. Puede deberse a condiciones climáticas, problemas técnicos, congestión aérea u otras circunstancias operativas.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Directo</h3>
                        <p class="mb-3">Vuelo que viaja de origen a destino sin escalas intermedias. Es la opción más rápida y cómoda para el pasajero.</p>

                        <!-- E -->
                        <h2 class="h5 fw-bold text-dark mt-4">E</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Embarque</h3>
                        <p class="mb-3">Acción de subir al avión. Comienza en la puerta de embarque indicada en la tarjeta de embarque y finaliza cuando el pasajero está sentado en su asiento.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Equipaje de mano</h3>
                        <p class="mb-3">Maleta o bolso que el pasajero lleva consigo en la cabina del avión. Debe cumplir con dimensiones y peso establecidos por la aerolínea.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Equipaje facturado</h3>
                        <p class="mb-3">Equipaje que se entrega en el mostrador de check-in y se transporta en la bodega del avión. Se recupera en el carrusel de equipaje del aeropuerto de destino.</p>

                        <!-- F -->
                        <h2 class="h5 fw-bold text-dark mt-4">F</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Flight Number / Número de vuelo</h3>
                        <p class="mb-3">Código único que identifica un vuelo específico. Generalmente consta de dos letras del código IATA de la aerolínea seguidas de 3 o 4 dígitos numéricos.</p>

                        <!-- I -->
                        <h2 class="h5 fw-bold text-dark mt-4">I</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Ida y vuelta</h3>
                        <p class="mb-3">Pasaje que incluye el viaje de ida hacia el destino y el regreso al origen. Las fechas de ambos vuelos se fijan al momento de la reserva.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Itinerario</h3>
                        <p class="mb-3">Plan detallado de un viaje que incluye vuelos, horarios, escalas y conexiones. Se proporciona al pasajero al confirmar la reserva.</p>

                        <!-- O -->
                        <h2 class="h5 fw-bold text-dark mt-4">O</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Overbooking</h3>
                        <p class="mb-3">Situación en la que la aerolínea vende más pasajes que asientos disponibles, anticipando que algunos pasajeros no se presentarán. Si todos viajan, se ofrecen compensaciones a quienes cedan su lugar.</p>

                        <!-- P -->
                        <h2 class="h5 fw-bold text-dark mt-4">P</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Pasajero</h3>
                        <p class="mb-3">Persona que viaja en un vuelo. Debe contar con documentación válida y haber completado el proceso de check-in y embarque según las regulaciones aplicables.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Promoción</h3>
                        <p class="mb-3">Oferta especial con descuento o beneficios adicionales en determinados vuelos o rutas. Suelen tener condiciones específicas como fechas límite o restricciones de cambio.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Puerta de embarque</h3>
                        <p class="mb-3">Lugar físico en el aeropuerto desde donde los pasajeros abordan el avión. Se indica en la tarjeta de embarque y puede cambiar según las condiciones operativas.</p>

                        <!-- R -->
                        <h2 class="h5 fw-bold text-dark mt-4">R</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Reserva</h3>
                        <p class="mb-3">Resguardo de un asiento en un vuelo específico. Se confirma mediante el pago y genera un código de reserva único que identifica la transacción.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Reprogramación</h3>
                        <p class="mb-3">Cambio en la fecha u horario de un vuelo por decisión de la aerolínea, generalmente por razones operativas o meteorológicas. No debe confundirse con una modificación solicitada por el pasajero.</p>

                        <!-- S -->
                        <h2 class="h5 fw-bold text-dark mt-4">S</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Solo ida</h3>
                        <p class="mb-3">Pasaje que incluye únicamente el viaje de ida hacia el destino, sin incluir el regreso al origen. Es ideal para viajes con fecha de retorno indefinida.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Standby</h3>
                        <p class="mb-3">Lista de espera para pasajeros que desean abordar un vuelo que ya está completo. Se asignan asientos si hay cancelaciones o no-shows (pasajeros que no se presentan).</p>

                        <!-- T -->
                        <h2 class="h5 fw-bold text-dark mt-4">T</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Tarifa</h3>
                        <p class="mb-3">Precio del pasaje que incluye condiciones específicas sobre cambios, cancelaciones, equipaje y otros servicios. Las tarifas más económicas suelen tener más restricciones.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Taxiing / Rodaje</h3>
                        <p class="mb-3">Movimiento del avión en tierra, desde la puerta de embarque hasta la pista de despegue o desde la pista hasta la puerta de desembarque tras el aterrizaje.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Tarjeta de embarque</h3>
                        <p class="mb-3">Documento emitido tras completar el check-in que contiene información esencial del vuelo: número de vuelo, asiento, puerta de embarque y hora de salida. Puede ser física o digital.</p>

                        <!-- V -->
                        <h2 class="h5 fw-bold text-dark mt-4">V</h2>
                        
                        <h3 class="h6 fw-bold text-dark mb-1">Vuelo</h3>
                        <p class="mb-3">Operación aérea programada que transporta pasajeros y/o carga desde un aeropuerto de origen hasta uno de destino. Se identifica con un número de vuelo único.</p>

                        <h3 class="h6 fw-bold text-dark mb-1">Vuelo charter</h3>
                        <p class="mb-3">Vuelo no programado regularmente, contratado por un grupo específico para una ruta y fecha determinadas. Generalmente utilizado por grupos, empresas o eventos especiales.</p>

                    </div>

                    <!-- Mensaje si no hay resultados -->
                    <div class="text-center d-none" id="sinResultados">
                        <i class="bi bi-search fs-1 text-muted mb-3"></i>
                        <h3 class="h5 fw-bold mb-2">No se encontraron términos</h3>
                        <p class="text-secondary">Intentá con otra palabra clave o revisá el listado completo.</p>
                        <button class="btn btn-outline-primary mt-3" onclick="limpiarBusqueda()" tabindex="0">Ver todos los términos</button>
                    </div>

                    <div class="mt-5 pt-3 border-top text-center">
                        <a href="<?= url('index.php?pagina=inicio') ?>" class="btn btn-outline-primary px-4 fw-semibold">
                            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Volver al Inicio
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- Footer unificado -->
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buscador = document.getElementById('buscadorGlosario');
            const container = document.getElementById('glosarioContainer');
            const sinResultados = document.getElementById('sinResultados');
            const terminos = container.querySelectorAll('h3');
            const titulosLetra = container.querySelectorAll('h2');

            buscador.addEventListener('input', function() {
                const terminoBusqueda = this.value.toLowerCase().trim();

                if (terminoBusqueda === '') {
                    // Mostrar todo
                    terminos.forEach(t => t.style.display = 'block');
                    titulosLetra.forEach(t => t.style.display = 'block');
                    container.style.display = 'block';
                    sinResultados.style.display = 'none';
                    return;
                }

                let hayResultados = false;

                // Ocultar todo primero
                terminos.forEach(t => t.style.display = 'none');
                titulosLetra.forEach(t => t.style.display = 'none');

                // Buscar y mostrar coincidencias
                terminos.forEach(termino => {
                    const texto = termino.textContent.toLowerCase();
                    const siguienteParrafo = termino.nextElementSibling;
                    
                    if (texto.includes(terminoBusqueda) || (siguienteParrafo && siguienteParrafo.textContent.toLowerCase().includes(terminoBusqueda))) {
                        termino.style.display = 'block';
                        if (siguienteParrafo) {
                            siguienteParrafo.style.display = 'block';
                        }
                        
                        // Mostrar el título de letra correspondiente
                        const tituloLetra = termino.previousElementSibling;
                        if (tituloLetra && tituloLetra.tagName === 'H2') {
                            tituloLetra.style.display = 'block';
                        }
                        
                        hayResultados = true;
                    } else {
                        if (siguienteParrafo) {
                            siguienteParrafo.style.display = 'none';
                        }
                    }
                });

                if (hayResultados) {
                    container.style.display = 'block';
                    sinResultados.style.display = 'none';
                } else {
                    container.style.display = 'none';
                    sinResultados.style.display = 'block';
                }
            });

            window.limpiarBusqueda = function() {
                buscador.value = '';
                buscador.dispatchEvent(new Event('input'));
                buscador.focus();
            };
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= url('public/js/accesibilidad.js') ?>" defer></script>
</body>
</html>
