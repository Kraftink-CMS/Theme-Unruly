const menuToggle = document.querySelector('[data-unruly-menu-toggle]');
const menu = menuToggle ? document.getElementById(menuToggle.getAttribute('aria-controls')) : null;

if (menuToggle && menu) {
    const mobile = window.matchMedia('(max-width: 800px)');
    const setExpanded = (expanded) => {
        menuToggle.setAttribute('aria-expanded', String(expanded));
        menu.classList.toggle('is-open', expanded);
    };
    const resetMenu = () => {
        setExpanded(false);
        menuToggle.hidden = !mobile.matches;
    };

    menu.dataset.unrulyCollapsible = 'true';
    menuToggle.addEventListener('click', () => {
        setExpanded(menuToggle.getAttribute('aria-expanded') !== 'true');
    });
    const closeOnEscape = (event) => {
        if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
            setExpanded(false);
            menuToggle.focus();
        }
    };
    menuToggle.addEventListener('keydown', closeOnEscape);
    menu.addEventListener('keydown', closeOnEscape);
    menu.addEventListener('click', (event) => {
        if (mobile.matches && event.target.closest('a')) {
            setExpanded(false);
        }
    });
    mobile.addEventListener('change', resetMenu);
    resetMenu();
}

for (const sword of document.querySelectorAll('[data-unruly-sword]')) {
    sword.addEventListener('click', () => {
        const active = sword.getAttribute('aria-pressed') === 'true';
        sword.setAttribute('aria-pressed', String(!active));
        sword.classList.toggle('is-diamond', !active);
    });
}
