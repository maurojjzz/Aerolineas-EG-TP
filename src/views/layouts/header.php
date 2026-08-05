<header class="container-xxl">
    <div class="fila_head row mt-3 d-flex align-items-center justify-content-between">

        <div class="col-2 d-flex d-md-none justify-content-center align-items-center">
            <?php require './src/views/components/menuMobile.php' ?>
        </div>
        
        <div class="col-1 d-flex justify-content-center align-items-center ">
            <a class="navbar-brand logoLink" href="#">
                <img class="logo" src="../../../public/img/aerologo.webp" alt="Logo de la pagina" >
            </a>
        </div>
        
        <div class="col d-none d-md-flex align-items-center justify-content-center">
                <ul class="nav navbar-custom d-flex overflow-hidden rounded-2">
                    <li class="nav-item active">
                        <a href="#" class="nav-link">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Vuelos</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Promociones</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Novedades</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Ayuda</a>
                    </li>
                </ul>
        </div>

        <div class="col-1 d-flex justify-content-center align-items-center">

            <button type="button" class="btn  d-md-none  btn-custom-perfil">
                <img src="../../../public/img/icons/usuario.png" alt="Icono de perfil" class="icono_perfil-mobile">
            </button>
            
            <button type="button" class="btn d-none d-md-flex px-lg-4 btn-custom">
                <img src="../../../public/img/icons/usuario.png" alt="Icono de perfil" class="icono_perfil">
                Ingresar
            </button>

            
        </div>
    </div>
</header>