document.addEventListener('DOMContentLoaded', () => {
    initMarketingReveal();
    initMarketingNav();
});

function initMarketingReveal() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.mkt-reveal').forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.08, rootMargin: '0px 0px -32px 0px' },
    );

    document.querySelectorAll('.mkt-reveal').forEach((el) => observer.observe(el));
}

function initMarketingNav() {
    const nav = document.getElementById('mkt-nav');
    const toggle = document.getElementById('mkt-nav-toggle');
    const panel = document.getElementById('mkt-nav-mobile');
    if (!nav) return;

    const lightSections = document.querySelectorAll('[data-nav-light]');
    const darkThreshold = 80;

    const updateNav = () => {
        let useLight = false;
        lightSections.forEach((section) => {
            const rect = section.getBoundingClientRect();
            if (rect.top <= darkThreshold && rect.bottom > darkThreshold) {
                useLight = true;
            }
        });
        if (!lightSections.length && window.scrollY > 400) {
            useLight = true;
        }
        nav.classList.toggle('mkt-nav--light', useLight);
        nav.classList.toggle('mkt-nav--dark', !useLight);
    };

    window.addEventListener('scroll', updateNav, { passive: true });
    updateNav();

    const setMenuOpen = (open) => {
        panel?.classList.toggle('is-open', open);
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('overflow-hidden', open);
    };

    toggle?.addEventListener('click', () => {
        setMenuOpen(!panel?.classList.contains('is-open'));
    });

    panel?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMenuOpen(false));
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 768px)').matches) {
            setMenuOpen(false);
        }
    });
}
