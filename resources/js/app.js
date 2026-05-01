import './bootstrap';

window.slugify = (value) => value
    .toString()
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');

document.addEventListener('DOMContentLoaded', () => {
    const loadScriptOnce = (id, src, attributes = {}) => {
        if (document.getElementById(id)) return;

        const script = document.createElement('script');
        script.id = id;
        script.src = src;
        script.async = true;

        Object.entries(attributes).forEach(([name, value]) => script.setAttribute(name, value));
        document.head.appendChild(script);
    };

    const loadConsentScripts = () => {
        const gaId = document.body.dataset.gaId;
        const adsenseClient = document.body.dataset.adsenseClient;

        if (gaId) {
            window.dataLayer = window.dataLayer || [];
            window.gtag = window.gtag || function gtag(){ window.dataLayer.push(arguments); };
            loadScriptOnce('google-analytics-script', `https://www.googletagmanager.com/gtag/js?id=${gaId}`);
            window.gtag('js', new Date());
            window.gtag('config', gaId, { anonymize_ip: true });
        }

        if (adsenseClient) {
            loadScriptOnce('adsense-script', `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${adsenseClient}`, { crossorigin: 'anonymous' });
        }
    };

    const syncThemeControls = () => {
        const isDark = document.documentElement.classList.contains('dark');

        document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
            toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        });

        document.querySelectorAll('[data-theme-icon="moon"]').forEach((icon) => icon.classList.toggle('hidden', isDark));
        document.querySelectorAll('[data-theme-icon="sun"]').forEach((icon) => icon.classList.toggle('hidden', !isDark));
    };

    document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            syncThemeControls();
        });
    });

    syncThemeControls();

    const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    const setMobileMenu = (open) => {
        mobileMenuButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
        mobileMenu?.classList.toggle('hidden', !open);
        document.body.classList.toggle('menu-open', open);
    };

    mobileMenuButton?.addEventListener('click', () => {
        setMobileMenu(mobileMenuButton.getAttribute('aria-expanded') !== 'true');
    });

    document.querySelectorAll('[data-mobile-menu-link]').forEach((link) => {
        link.addEventListener('click', () => setMobileMenu(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setMobileMenu(false);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1280) setMobileMenu(false);
    }, { passive: true });

    const backToTop = document.getElementById('back-to-top');
    const siteHeader = document.getElementById('site-header');

    window.addEventListener('scroll', () => {
        backToTop?.classList.toggle('hidden', window.scrollY < 300);
        siteHeader?.classList.toggle('shadow-lg', window.scrollY > 10);
    }, { passive: true });

    backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    const cookieConsent = document.getElementById('cookie-consent');
    const cookieAccept = document.getElementById('cookie-accept');

    if (document.cookie.includes('yb_cookie_consent=accepted')) {
        loadConsentScripts();
    } else {
        cookieConsent?.classList.remove('hidden');
    }

    cookieAccept?.addEventListener('click', () => {
        document.cookie = 'yb_cookie_consent=accepted; max-age=31536000; path=/; SameSite=Lax';
        cookieConsent?.classList.add('hidden');
        loadConsentScripts();
    });

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
                status.className = 'newsletter-status text-sm font-bold text-[var(--accent)] sm:col-span-2';
            } catch (error) {
                status.textContent = 'Something went wrong. Try again or email contact@youssefyouyou.com';
                status.className = 'newsletter-status text-sm font-bold text-red-200 sm:col-span-2';
            }
        });
    });
});
