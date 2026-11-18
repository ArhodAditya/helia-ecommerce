const themeToggle = document.getElementById('themeToggle');

function getSavedTheme() {
    return localStorage.getItem('theme') || 'light';
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);

    if (!themeToggle) {
        return;
    }

    if (theme === 'dark') {
        themeToggle.innerHTML = '<i class="bi bi-sun"></i> Light';
        themeToggle.setAttribute('aria-label', 'Switch to light mode');
    } else {
        themeToggle.innerHTML = '<i class="bi bi-moon-stars"></i> Dark';
        themeToggle.setAttribute('aria-label', 'Switch to dark mode');
    }
}

if (themeToggle) {
    themeToggle.addEventListener('click', function () {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme);
    });
}

applyTheme(getSavedTheme());

function showButtonLoading(button) {
    const hasText = button.textContent.trim() !== '';

    button.dataset.originalHtml = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>' + (hasText ? ' Please wait...' : '');
    button.disabled = true;
}

function resetButton(button) {
    if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
        delete button.dataset.originalHtml;
    }

    button.disabled = false;
}

document.querySelectorAll('form').forEach(function (form) {
    const isPostForm = form.getAttribute('method') && form.getAttribute('method').toLowerCase() === 'post';

    if (!form.classList.contains('needs-loading') && !isPostForm) {
        return;
    }

    form.addEventListener('submit', function (event) {
        if (event.defaultPrevented) {
            return;
        }

        const buttons = form.querySelectorAll('button[type="submit"], button:not([type])');

        setTimeout(function () {
            buttons.forEach(showButtonLoading);
        }, 0);

        setTimeout(function () {
            buttons.forEach(resetButton);
        }, 8000);
    });
});

window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        document.querySelectorAll('button[data-original-html]').forEach(resetButton);
    }
});
