document.addEventListener('DOMContentLoaded', () => {
    const menu = document.getElementById('mobileMenu');
    const sidebar = document.getElementById('sidebar');

    if (menu && sidebar) {
        menu.addEventListener('click', () => sidebar.classList.toggle('open'));
        document.addEventListener('click', (event) => {
            if (window.innerWidth <= 900 && sidebar.classList.contains('open') &&
                !sidebar.contains(event.target) && !menu.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (event) => {
            if (!confirm(el.dataset.confirm)) event.preventDefault();
        });
    });

    document.querySelectorAll('.money-input').forEach(input => {
        input.addEventListener('input', () => {
            let value = input.value.replace(/\D/g, '');
            value = (Number(value) / 100).toFixed(2);
            input.value = value;
        });
    });

    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            if (el.classList.contains('alert-success')) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-5px)';
                setTimeout(() => el.remove(), 300);
            }
        });
    }, 4500);
});
