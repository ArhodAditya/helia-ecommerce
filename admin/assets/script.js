const themeToggle = document.getElementById('themeToggle');

function updateThemeButton() {
    const theme = document.documentElement.getAttribute('data-bs-theme');

    if (theme === 'dark') {
        themeToggle.innerHTML = '<i class="bi bi-sun"></i> Light';
    } else {
        themeToggle.innerHTML = '<i class="bi bi-moon-stars"></i> Dark';
    }
}

themeToggle.addEventListener('click', function () {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeButton();

    document.dispatchEvent(new Event('themechange'));
});

updateThemeButton();
