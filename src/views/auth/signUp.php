<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro de Usuario</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">



        <link rel="stylesheet" href="<?= url('public/css/layout/signUp.css') ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">

    </head>

    <body class="container-fluid overflow-x-hidden p-0 m-0 d-flex flex-column align-items-center"> 
        <?php require './src/views/layouts/header.php' ?>
        
        <section class="container-xxl main-section row p-0 m-0">
            <div class="d-none d-lg-flex col-lg-4  mt-5 pt-3  h-100">
                
                <div class="hero-content text-left mt-5 user-select-none">
                    <h5 class="fw-bold">Vuela sin limites</h5>
                    <h2 >CREA TU <span>CUENTA</span> </h2>
                    <p>Registrate y comienza a reservar vuelos, gestionar tus reservas y vivir nuevas experiencias.</p>
                </div>
            </div>

            <div class="col-12 col-lg-8 d-flex flex-column align-items-center justify-content-center pt-5 pt-sm-0 ">
                <form action="#" id="formRegistro" class="formContainer rounded-3 shadow-lg p-4  " >
                    <h3 class="fs-1 fw-semibold titleForm">Registrarme como usuario</h3>
                    <p class="text-muted text-break">Completa tus datos y te enviaremos un correo de validacion para activar tu cuenta.</p>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-4 ">
                        <div class="form-group d-flex flex-column p-0  w-100">
                            <label class="form-label-t" id="labNombre" for="nombre">Nombre:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control ctm-inp" required placeholder="Ingrese su nombre">
                            <p id="infoNombre" class=" text-break text-center form-text-info text-danger fs-6"></p>
                        </div>
                        
                        <div class="form-group d-flex flex-column w-100">
                            <label class="form-label-t" id="labApellido" for="apellido">Apellido:</label>
                            <input type="text" name="apellido" id="apellido" class="form-control ctm-inp" required placeholder="Ingrese su apellido">
                            <p id="infoApellido" class="text-break text-center form-text-info text-danger fs-6"></p>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-4 ">
                        <div class="form-group d-flex flex-column p-0  w-100 ">
                            <label class="form-label-t" id="labNumeroDocumento" for="numero_documento">N° de documento:</label>
                            <div class="input-group">
                                <select class="form-select w-25" name="tipo_documento" id="tipo_documento">
                                    <option value="DNI" selected>DNI</option>
                                    <option value="pasaporte">Pasaporte</option>
                                    <option value="lc">LC</option>
                                    <option value="le">LE</option>
                                </select>

                                <input
                                    type="text"
                                    class="form-control w-75"
                                    name="numero_documento"
                                    id="numero_documento"
                                    placeholder="Número de documento"
                                    required
                                >
                            </div>
                            <p id="infoNumeroDocumento" class="text-break text-center form-text-info text-danger fs-6"></p>
                        </div>
                        
                        <div class="form-group d-flex flex-column w-100">
                            <label class="form-label-t" id="labEmail" for="email">Email:</label>
                            <input type="email" name="email" id="email" class="form-control ctm-inp" required placeholder="Ingrese su email">
                            <p id="infoEmail" class="text-break text-center form-text-info text-danger fs-6"></p>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-4 ">
                        <div class="form-group d-flex flex-column p-0  w-100">
                            <label class="form-label-t" id="labTelefono" for="telefono">Telefono:</label>
                            <input type="tel" name="telefono" id="telefono" class="form-control ctm-inp" required placeholder="Ingrese su telefono">
                            <p id="infoTelefono" class=" text-break text-center form-text-info text-danger fs-6"></p>
                        </div>
                        
                        <div class="form-group d-flex flex-column w-100">
                            <label class="form-label-t" id="labFechaNacimiento" for="fecha_nacimiento">Fecha de nacimiento:</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control ctm-inp" required placeholder="Ingrese su fecha de nacimiento" max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                            <p id="infoFechaNacimiento" class="text-break text-center form-text-info text-danger fs-6"></p>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-4">

                        <div class="form-group d-flex flex-column p-0 w-100">
                            <label class="form-label-t" id="labContrasena" for="contrasena">Contraseña:</label>

                            <input type="password" name="contrasena" id="contrasena" class="form-control ctm-inp" required placeholder="Ingrese su contraseña">

                            <p id="infoContrasena" class="text-break text-center form-text-info text-danger fs-6"></p>
                        </div>

                        <div class="form-group d-flex flex-column w-100">
                            <label class="form-label-t" id="labConfirmarContrasena" for="confirmar_contrasena">Confirmar contraseña:</label>

                            <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-control ctm-inp" required placeholder="Repita su contraseña">

                            <p id="infoConfirmarContrasena" class="text-break text-center form-text-info text-danger fs-6"></p>
                        </div>
                    </div>

                    <button type="submit" class="btn btnLogin py-2 w-100 d-flex align-items-center justify-content-center gap-2 mt-3">
                        <img src=" <?= url('public/img/icons/agregar-usuario.png') ?>" alt="Icono de crear usuario" class="iconoBtnLgn">
                        <span class="fs-5 fw-medium">Crear Usuario</span>
                    </button>

                    <a href="#" class="d-block text-decoration-none text-center fs-6 mt-4" ><span class="text-dark">¿Ya tienes cuenta?</span> Inicia sesión</a>
                    
                </form>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

        <script src="<?= url('public/js/admin/validacionRegistro.js') ?>"></script>


    </body>

</html>