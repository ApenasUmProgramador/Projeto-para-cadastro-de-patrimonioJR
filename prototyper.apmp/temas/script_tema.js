// Executa o código quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    
    // Lógica para o menu da sidebar (exemplo)
    const menuIcon = document.getElementById('menu-icon');
    const sidebar = document.getElementById('sidebar');
    if (menuIcon && sidebar) {
        menuIcon.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-open');
        });
    }

    // --- LÓGICA DO MODO ESCURO ---
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const body = document.body;

    // Função para aplicar o tema salvo
    const applyTheme = () => {
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            if (darkModeToggle) {
                darkModeToggle.checked = true;
            }
        } else {
            body.classList.remove('dark-mode');
            if (darkModeToggle) {
                darkModeToggle.checked = false;
            }
        }
    };

    // Aplica o tema assim que a página carrega
    applyTheme();

    // Adiciona o evento de clique apenas se o botão existir na página
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

    // Lógica de logout (exemplo)
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('username');
            localStorage.removeItem('darkMode'); // Limpa também o tema
            window.location.href = 'index.html';
        });
    }
});