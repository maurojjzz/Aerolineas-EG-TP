/**
 * Carrusel de Promociones destacadas del home público.
 *
 * Funciona con scroll nativo + scroll-snap: las flechas avanzan una página,
 * los puntos se generan según las cards visibles y, si no hay overflow,
 * se ocultan flechas y puntos.
 */
(() => {
    const carousels = document.querySelectorAll('[data-carousel]');
    if (!carousels.length) return;

    const VISIBILITY_TOLERANCE = 2;

    carousels.forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const dots  = carousel.querySelector('[data-carousel-dots]');
        const status = carousel.querySelector('[data-carousel-status]');
        const prev  = carousel.querySelector('[data-carousel-prev]');
        const next  = carousel.querySelector('[data-carousel-next]');

        if (!track || !dots) return;

        let pages   = 1;
        let visible = 1;
        let step    = track.clientWidth || 1;

        const getStep = () => {
            const first = track.firstElementChild;
            if (!first) return track.clientWidth || 1;

            const styles = window.getComputedStyle(track);
            const gap    = parseFloat(styles.columnGap || styles.gap) || 0;

            return first.getBoundingClientRect().width + gap;
        };

        const getVisibleCards = () => {
            step = getStep();
            if (step <= 0) return 1;

            return Math.max(1, Math.round(track.clientWidth / step));
        };

        const pageTarget = (index) => {
            const max = Math.max(0, track.scrollWidth - track.clientWidth);
            const clamped = Math.max(0, Math.min(index, pages - 1));

            return Math.min(clamped * visible * step, max);
        };

        const currentPage = () => {
            let best = 0;
            let bestDistance = Infinity;

            for (let i = 0; i < pages; i += 1) {
                const distance = Math.abs(track.scrollLeft - pageTarget(i));
                if (distance < bestDistance) {
                    bestDistance = distance;
                    best = i;
                }
            }

            return best;
        };

        const goTo = (index) => {
            const target = Math.max(0, Math.min(index, pages - 1));
            track.scrollTo({ left: pageTarget(target), behavior: 'smooth' });

            if (status) {
                status.textContent = `Página ${target + 1} de ${pages} de promociones destacadas`;
            }

            // Refresca los puntos aunque el navegador no emita el scroll
            // (por ejemplo, si el movimiento ya estaba en el destino).
            window.setTimeout(update, 520);
        };

        const update = () => {
            const page   = currentPage();
            const buttons = dots.querySelectorAll('.home-carousel-dot');

            buttons.forEach((dot, index) => {
                const active = index === page;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });

            if (prev) prev.disabled = page <= 0;
            if (next) next.disabled = page >= pages - 1;
        };

        const buildDots = () => {
            visible = getVisibleCards();
            const total = track.children.length;

            pages = Math.max(1, Math.ceil(total / visible));
            dots.innerHTML = '';

            const hasOverflow = track.scrollWidth > track.clientWidth + VISIBILITY_TOLERANCE;

            if (pages <= 1 || !hasOverflow) {
                carousel.classList.add('is-static');
                return;
            }

            carousel.classList.remove('is-static');

            for (let i = 0; i < pages; i += 1) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'home-carousel-dot';
                dot.setAttribute('aria-label', `Ir a la página ${i + 1} de ${pages}`);
                dot.addEventListener('click', () => goTo(i));
                dots.appendChild(dot);
            }
        };

        if (prev) {
            prev.addEventListener('click', () => goTo(currentPage() - 1));
        }

        if (next) {
            next.addEventListener('click', () => goTo(currentPage() + 1));
        }

        // Navegación por teclado dentro de la región del carrusel
        track.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                goTo(currentPage() + 1);
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goTo(currentPage() - 1);
            } else if (event.key === 'Home') {
                event.preventDefault();
                goTo(0);
            } else if (event.key === 'End') {
                event.preventDefault();
                goTo(pages - 1);
            }
        });

        let scrollTimer = null;
        track.addEventListener('scroll', () => {
            window.clearTimeout(scrollTimer);
            scrollTimer = window.setTimeout(update, 90);
        }, { passive: true });

        if ('onscrollend' in window) {
            track.addEventListener('scrollend', update, { passive: true });
        }

        let resizeTimer = null;
        window.addEventListener('resize', () => {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(() => {
                buildDots();
                update();
            }, 150);
        });

        buildDots();
        update();
    });
})();
