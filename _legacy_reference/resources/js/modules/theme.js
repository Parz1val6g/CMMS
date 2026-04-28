/**
 * Theme Toggle Module
 * Handles dark/light mode switching and persistence
 */

export function initTheme() {
    const toggleBtn = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    const iconSun = document.getElementById('icon-sun');
    const iconMoon = document.getElementById('icon-moon');

    // Verifica se o utilizador já tinha escolhido Dark Mode antes
    const savedTheme = localStorage.getItem('saas-theme') || 'light';
    setTheme(savedTheme);

    // Evento do Clique no botão
    toggleBtn?.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
    });

    /**
     * Apply theme and update icons
     */
    function setTheme(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('saas-theme', theme);

        if (theme === 'dark') {
            iconSun?.classList.add('d-none');
            iconMoon?.classList.remove('d-none');
        } else {
            iconMoon?.classList.add('d-none');
            iconSun?.classList.remove('d-none');
        }
    }
}
