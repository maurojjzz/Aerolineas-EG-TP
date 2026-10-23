document.addEventListener("DOMContentLoaded", function () {
    const menuDropdown = document.querySelector("#dropdownNotificaciones + .dropdown-menu");
    const btnCampana = document.getElementById("dropdownNotificaciones");

    if (!btnCampana || !menuDropdown) return;

    const actualizarNotificaciones = async () => {
        try {
            const response = await fetch(URL_NOTIFICACIONES);
            if (!response.ok) return;
            
            const data = await response.json();
            if (data.error) return;

            const total = data.total;
            let badgeCampana = btnCampana.querySelector(".badge");

            if (total > 0) {
                if (badgeCampana) {
                    badgeCampana.textContent = total;
                    badgeCampana.style.display = "inline-block";
                } else {
                    const newBadge = document.createElement("span");
                    newBadge.className = "position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger";
                    newBadge.style.fontSize = "0.65rem";
                    newBadge.textContent = total;
                    btnCampana.appendChild(newBadge);
                }
            } else {
                if (badgeCampana) {
                    badgeCampana.style.display = "none";
                }
            }

            let htmlContent = `<li><div class="dropdown-header fw-bold text-dark border-bottom pb-2 px-2">Notificaciones y Pendientes</div></li>`;

            if (total === 0) {
                htmlContent += `<li><span class="dropdown-item-text text-center text-muted py-3 small">No hay notificaciones pendientes.</span></li>`;
            } else {
                data.ceos.forEach(ceo => {
                    const fecha = new Date(ceo.fechaCreacion).toLocaleDateString('es-AR');
                    htmlContent += `
                        <li>
                            <a class="dropdown-item rounded-2 py-2 border-bottom text-wrap d-flex flex-column gap-1" 
                               href="${URL_LISTADO_CEOS}"
                               data-tipo="ceo" 
                               data-id="${ceo.id}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge text-bg-warning text-dark" style="font-size: 0.7rem;">Nuevo CEO</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">${fecha}</small>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.85rem;">${ceo.nombre} ${ceo.apellido}</span>
                                <span class="text-muted text-truncate" style="font-size: 0.75rem;">Requiere habilitación en el sistema.</span>
                            </a>
                        </li>`;
                });

                data.promociones.forEach(promo => {
                    htmlContent += `
                        <li>
                            <a class="dropdown-item rounded-2 py-2 border-bottom text-wrap d-flex flex-column gap-1" 
                               href="${URL_LISTADO_PROMOCIONES}"
                               data-tipo="promocion" 
                               data-id="${promo.id}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge text-bg-info text-white" style="font-size: 0.7rem;">Promoción</span>
                                    <span class="text-success fw-bold" style="font-size: 0.75rem;">${promo.codigo}</span>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.85rem;">${promo.nombre}</span>
                                <span class="text-muted text-truncate" style="font-size: 0.75rem;">Aerolínea: ${promo.nombreAerolinea}</span>
                            </a>
                        </li>`;
                });
            }

            menuDropdown.innerHTML = htmlContent;

        } catch (error) {
            console.error("Error al actualizar notificaciones:", error);
        }
    };

    // 🚀 1. LLAMADA INMEDIATA: Se ejecuta ni bien carga la página sin esperar 10 segundos
    actualizarNotificaciones();

    // ⏱️ 2. BUCLE: Se vuelve a ejecutar cada 10 segundos de manera automática
    setInterval(actualizarNotificaciones, 10000);

    menuDropdown.addEventListener("click", async function (e) {
        const linkItem = e.target.closest(".dropdown-item");
        if (linkItem) {
            e.preventDefault();
            const tipo = linkItem.dataset.tipo;
            const id = linkItem.dataset.id;
            const targetUrl = linkItem.href;

            if (tipo && id) {
                try {
                    await fetch(`${URL_MARCAR_NOTIFICACION}?tipo=${tipo}&id=${id}`);
                } catch (err) {
                    console.error("Error al marcar notificación", err);
                }
            }
            window.location.href = targetUrl;
        }
    });
});