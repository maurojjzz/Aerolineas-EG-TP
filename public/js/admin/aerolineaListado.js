(function () {
    const tablaBody = document.getElementById("aerolineaTableBody");
    if (!tablaBody) return; 

    const estado = {
        pagina: 1,
        porPagina: 10,
        filtroEstado: '',
        filtroPais: '',
        filtroConCeo: '',
        busqueda: ''
    };

    async function cargarAerolineas() {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 text-muted">Cargando...</span>
                </td>
            </tr>`;

        const params = new URLSearchParams({
            accion:'listar',
            pagina: estado.pagina,
            porPagina:estado.porPagina,
            estado: estado.filtroEstado,
            pais: estado.filtroPais,
            conCeo: estado.filtroConCeo,
            busqueda: estado.busqueda
        });

        try {
            const res  = await fetch(`src/routes/aerolinea.php?${params}`);
            const json = await res.json();
            renderTabla(json.data);
            renderPaginacion(json.pagina, json.totalPaginas, json.total, json.porPagina);
        } catch (err) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        Error al cargar los datos. Intentá de nuevo.
                    </td>
                </tr>`;
        }
    }

    function renderTabla(aerolineas) {
        if (!aerolineas.length) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        No hay aerolíneas que coincidan.
                    </td>
                </tr>`;
            return;
        }

        tablaBody.innerHTML = aerolineas.map(a => `
            <tr>
                <td class="text-center">${escHtml(a.codigoIATA)}</td>
                <td>${escHtml(a.nombreAerolinea)}</td>
                <td>${escHtml(a.codPais)}</td>
                <td>
                    <span class="badge text-bg-${a.activo == 1 ? 'success' : 'secondary'}">
                        ${a.activo == 1 ? 'Activa' : 'Inactiva'}
                    </span>
                </td>
                <td>
                    ${a.ceoNombre
                        ? escHtml(a.ceoNombre + ' ' + a.ceoApellido)
                        : '<span class="text-muted">—</span>'
                    }
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-evenly">
                        <button type="button" class="btn-accion" title="Ver" data-id="${a.idAerolinea}" data-accion="ver">
                            <img src="public/img/icons/readTabla.png" class="btn-tabla" alt="ver">
                        </button>
                        <button type="button" class="btn-accion" title="Editar" data-id="${a.idAerolinea}" data-accion="editar">
                            <img src="public/img/icons/lapiz-editar.png" class="btn-tabla" alt="editar">
                        </button>
                        <button type="button" class="btn-accion" title="Desactivar" data-id="${a.idAerolinea}" data-accion="eliminar">
                            <img src="public/img/icons/trash.png" class="btn-tabla" alt="desactivar">
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderPaginacion(paginaActual, totalPaginas, total, porPagina) {
        const desde = total === 0 ? 0 : (paginaActual - 1) * porPagina + 1;
        const hasta = Math.min(paginaActual * porPagina, total);

        document.getElementById("paginacionInfo").textContent =
            `Mostrando ${desde} a ${hasta} de ${total} aerolíneas`;

        const ul = document.getElementById("paginacionBotones");
        ul.innerHTML = '';

        // Helper para crear un <li> de paginación
        const crearLi = (texto, pagina, deshabilitado = false, activo = false) => {
            const li  = document.createElement('li');
            li.className = `page-item${deshabilitado ? ' disabled' : ''}${activo ? ' active' : ''}`;

            const btn = document.createElement('button');
            btn.className = 'page-link';
            btn.innerHTML = texto;

            if (!deshabilitado && !activo) {
                btn.addEventListener('click', () => {
                    estado.pagina = pagina;
                    cargarAerolineas();
                });
            }

            li.appendChild(btn);
            ul.appendChild(li);
        };

        crearLi('«', 1,            paginaActual === 1);
        crearLi('‹', paginaActual - 1, paginaActual === 1);

        // Máximo 5 botones numéricos centrados en la página actual
        const rango = 2;
        const inicio = Math.max(1, paginaActual - rango);
        const fin    = Math.min(totalPaginas, paginaActual + rango);

        if (inicio > 1) crearLi('...', inicio - 1, true);

        for (let i = inicio; i <= fin; i++) {
            crearLi(i, i, false, i === paginaActual);
        }

        if (fin < totalPaginas) crearLi('...', fin + 1, true);

        crearLi('›', paginaActual + 1, paginaActual === totalPaginas);
        crearLi('»', totalPaginas,     paginaActual === totalPaginas);
    }

    function escHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    document.getElementById("selectPorPagina").addEventListener("change", function () {
        estado.porPagina = parseInt(this.value);
        estado.pagina = 1;
        cargarAerolineas();
    });

    let debounceTimer;
    document.getElementById("buscadorAerolinea").addEventListener("input", function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            estado.busqueda = this.value.trim();
            estado.pagina = 1;
            cargarAerolineas();
        }, 350);
    });

    tablaBody.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-accion");
        if (!btn) return;

        const id     = btn.dataset.id;
        const accion = btn.dataset.accion;

        if (accion === "ver")      console.log("ver", id);     // → redirigir o abrir modal
        if (accion === "editar")   console.log("editar", id);  // → redirigir a editar
        if (accion === "eliminar") console.log("eliminar", id); // → confirmar y hacer POST
    });

    cargarAerolineas();

})();