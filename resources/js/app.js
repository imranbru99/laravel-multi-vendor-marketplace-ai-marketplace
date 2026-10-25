import './bootstrap';
// Native forms work without JavaScript. Prevent accidental repeated submissions.
document.addEventListener('submit', event => {
    if (event.target.hasAttribute('wire:submit')) return;
    const button = event.target.querySelector('button[type="submit"]');
    if (button) { setTimeout(() => { button.disabled = true; }, 0); }
});

const storedTheme = localStorage.getItem('marketplace-theme');
if (storedTheme === 'dark') document.documentElement.dataset.theme = 'dark';
document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('marketplace-theme', next);
}));
