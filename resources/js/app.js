import './bootstrap';

window.slugify = (value) => value
    .toString()
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.content-body pre').forEach((pre) => {
        if (pre.parentElement?.classList.contains('code-shell')) return;

        const shell = document.createElement('div');
        shell.className = 'code-shell relative my-6';
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'absolute right-3 top-3 rounded-lg bg-white/10 px-3 py-1 text-xs font-bold text-white';
        button.textContent = 'Copy';
        pre.parentNode.insertBefore(shell, pre);
        shell.appendChild(button);
        shell.appendChild(pre);
        button.addEventListener('click', async () => {
            await navigator.clipboard.writeText(pre.innerText);
            button.textContent = 'Copied';
            setTimeout(() => button.textContent = 'Copy', 1200);
        });
    });

    document.querySelectorAll('.newsletter-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const status = form.querySelector('.newsletter-status');
            status.classList.remove('hidden');

            if (form.querySelector('[name="website"]').value) return;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) throw new Error('Request failed');
                form.reset();
                status.textContent = "You're in. Expect one useful email per week, nothing else.";
                status.className = 'newsletter-status text-sm font-bold text-blue-200 sm:col-span-2';
            } catch (error) {
                status.textContent = 'Something went wrong. Try again or email contact@youssefyouyou.com';
                status.className = 'newsletter-status text-sm font-bold text-red-200 sm:col-span-2';
            }
        });
    });
});
