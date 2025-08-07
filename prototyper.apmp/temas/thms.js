document.addEventListener('DOMContentLoaded', () => {
    // --- LÓGICA DO MENU SIDEBAR ---
    const menuIcon = document.getElementById('menu-icon');
    const sidebar = document.getElementById('sidebar');
    if (menuIcon && sidebar) {
        menuIcon.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-open');
        });
    }

    // --- LÓGICA DE LOGOUT ---
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.clear(); // Limpa tudo
            window.location.href = 'index.html';
        });
    }

    // --- LÓGICA DO MODO ESCURO ---
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const body = document.body;

    const applyTheme = () => {
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            if (darkModeToggle) darkModeToggle.checked = true;
        } else {
            body.classList.remove('dark-mode');
            if (darkModeToggle) darkModeToggle.checked = false;
        }
    };

    applyTheme(); // Aplica o tema ao carregar a página

    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', () => {
            if (darkModeToggle.checked) {
                body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'disabled');
            }
        });
    }

    // --- TRANSIÇÃO DE OPACIDADE ---
    setTimeout(() => {
        body.classList.add('loaded');
    }, 100);
});