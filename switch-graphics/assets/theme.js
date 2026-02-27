(function () {
    const menu = document.getElementById('sgSideMenu');
    const overlay = document.getElementById('sgMenuOverlay');
    const toggle = document.getElementById('sgMenuToggle');
    const closeBtn = document.getElementById('sgMenuClose');

    if (!menu || !overlay || !toggle) {
        return;
    }

    const openMenu = function () {
        menu.classList.add('is-open');
        overlay.classList.add('is-open');
        menu.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sg-menu-open');
    };

    const closeMenu = function () {
        menu.classList.remove('is-open');
        overlay.classList.remove('is-open');
        menu.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sg-menu-open');
    };

    toggle.addEventListener('click', function () {
        if (menu.classList.contains('is-open')) {
            closeMenu();
            return;
        }
        openMenu();
    });

    overlay.addEventListener('click', closeMenu);
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMenu);
    }

    menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
})();
