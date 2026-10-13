
const Aeropuertos = (function () {
    let lista = [];
    let cargando = null;

    // Carga (o espera si ya está cargando)
    function cargar() {
        if (lista.length) return Promise.resolve(lista);
        if (cargando) return cargando;

        cargando = fetch('index.php?pagina=aeropuertos-json')
            .then(r => r.json())
            .then(data => {
                lista = data;
                cargando = null;
                return lista;
            })
            .catch(err => {
                console.error('Error cargando aeropuertos:', err);
                cargando = null;
                return [];
            });

        return cargando;
    }

    // Busca los aeropuertos que matcheen con el texto
    function buscar(texto) {
        if (!texto) return [];
        const t = texto.trim().toLowerCase();
        return lista.filter(a =>
            a.code.toLowerCase().includes(t) ||
            a.city.toLowerCase().includes(t) ||
            a.country.toLowerCase().includes(t)
        );
    }

    // Devuelve uno por código IATA
    function porCodigo(code) {
        return lista.find(a => a.code.toUpperCase() === code.toUpperCase());
    }

    return { cargar, buscar, porCodigo, getLista: () => lista };
})();