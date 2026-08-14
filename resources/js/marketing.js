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
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    document.querySelectorAll('.mkt-reveal').forEach((el) => observer.observe(el));
}

function initMarketingNav() {
    const toggle = document.getElementById('mkt-nav-toggle');
    const panel = document.getElementById('mkt-nav-mobile');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', () => {
        panel.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', panel.classList.contains('is-open'));
    });

    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => panel.classList.remove('is-open'));
    });
}
