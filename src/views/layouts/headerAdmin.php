<div class="container-header d-flex align-items-center bg-light row border-bottom border-2 w-100 m-0 p-0 g-0">
    <button type="button" class=" col-1 btn ctm-btn-header d-none d-md-block" id="sidebarToggleHamburguesa">
        <img src="../../../public/img/icons/menu-hamburguesa.png" alt="icnno menu hamburguesa" class="logo-header-admin opacity-75"> 
    </button>

    <!-- menu hamburguesa para mobile -->
    <button 
        type="button" 
        class=" col-1 btn ctm-btn-header d-md-none" 
        data 
        id="sidebarToggleHamburguesaMobile"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasAdmin"
        aria-controls="offcanvasAdmin"   
    >
            <img src="../../../public/img/icons/menu-hamburguesa.png" alt="icnno menu hamburguesa" class="logo-header-admin opacity-75"> 
    </button>

    <h5 class="col m-0 p-0">
        Panel de Administración
    </h5>

    <div class="col ctm-user-info d-flex justify-content-end align-items-center gap-2 pe-4 position-relative">

        <button type="button" class="btn btn-bell position-relative p-0 m-0 me-4">
            <img src="../../../public/img/icons/bell.png" alt="icono campana notificacion" class="bell-noti opacity-75"> 
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                1
                <span class="visually-hidden">unread messages</span>
            </span>
        </button>

        <div class="pfp-icon d-flex align-items-center justify-content-center rounded-circle">
            AJ
        </div>

        <div>
            <p class="m-0 p-0 ctm-rol">Administrador</p>
            <p class="m-0 p-0 ctm-name">Name LastN</p>
        </div>

        <span class="down-arrow position-absolute">⌄</span>

    </div>


</div>
