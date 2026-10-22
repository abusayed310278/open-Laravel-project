<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    <style>
        #auth-card {
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased selection:bg-brand-500 selection:text-white">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <a href="{{ route('home') }}" class="mb-8 transition-transform duration-200 hover:scale-105 active:scale-95">
            <x-brand-logo size="lg" />
        </a>

        <div id="auth-card" class="w-full {{ $maxWidth ?? 'max-w-md' }} bg-white border border-gray-100 rounded-md {{ $cardPadding ?? 'p-8' }} shadow-xs">
            @yield('content')
        </div>
    </div>

    <script>
        (function() {
            const cache = new Map();
            const DURATION = 220;
            const EASE = 'cubic-bezier(0.4, 0, 0.2, 1)';
            const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
            let animating = false;

            // Prefetch target page in background
            function prefetchUrl(url) {
                if (!url || cache.has(url)) return;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.text())
                    .then(html => cache.set(url, parseAuthPage(html)))
                    .catch(() => {});
            }

            function parseAuthPage(html) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCard = doc.getElementById('auth-card');
                const title = doc.querySelector('title')?.innerText || '';
                if (!newCard) throw new Error('Card element not found');
                return { html: newCard.innerHTML, className: newCard.className, title };
            }

            // Preload opposite route on idle so clicking is instantaneous
            function preloadCounterpart() {
                const path = window.location.pathname;
                if (path.includes('/login')) {
                    prefetchUrl('{{ route("register") }}');
                } else if (path.includes('/register')) {
                    prefetchUrl('{{ route("login") }}');
                }
            }

            // Execute script tags in swapped content safely within an IIFE
            function runScripts(container) {
                const scripts = container.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.textContent = '(function(){\n' + oldScript.textContent + '\n})();';
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }

            async function swapAuthPage(url, push = true) {
                if (animating) return;
                const card = document.getElementById('auth-card');
                if (!card) {
                    window.location.href = url;
                    return;
                }

                animating = true;
                card.style.transition = `opacity ${DURATION}ms ${EASE}`;
                card.style.opacity = '0';

                try {
                    const fetchData = (async () => {
                        let data = cache.get(url);
                        if (!data) {
                            const res = await fetch(url);
                            data = parseAuthPage(await res.text());
                            cache.set(url, data);
                        }
                        return data;
                    })();

                    // Always let the fade-out finish before swapping, even on a cache hit
                    const [data] = await Promise.all([fetchData, wait(DURATION)]);

                    if (push) {
                        window.history.pushState({ url: url }, '', url);
                    }
                    if (data.title) {
                        document.title = data.title;
                    }

                    const startHeight = card.offsetHeight;
                    card.style.height = startHeight + 'px';

                    card.className = data.className;
                    card.innerHTML = data.html;
                    runScripts(card);
                    attachAuthLinks();

                    card.style.height = 'auto';
                    const endHeight = card.offsetHeight;
                    card.style.height = startHeight + 'px';

                    requestAnimationFrame(() => {
                        card.style.transition = `opacity ${DURATION}ms ${EASE}, height ${DURATION}ms ${EASE}`;
                        card.style.height = endHeight + 'px';
                        card.style.opacity = '1';
                    });

                    await wait(DURATION + 30);
                    card.style.transition = '';
                    card.style.height = '';

                    preloadCounterpart();
                } catch (err) {
                    window.location.href = url;
                } finally {
                    animating = false;
                }
            }

            function attachAuthLinks() {
                document.querySelectorAll('a[href*="/login"], a[href*="/register"]').forEach(link => {
                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || link.target === '_blank') return;

                    link.addEventListener('mouseenter', () => prefetchUrl(href), { once: true });
                    link.addEventListener('touchstart', () => prefetchUrl(href), { once: true, passive: true });

                    link.addEventListener('click', (e) => {
                        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                        e.preventDefault();
                        swapAuthPage(href, true);
                    });
                });
            }

            window.addEventListener('popstate', () => {
                swapAuthPage(window.location.href, false);
            });

            document.addEventListener('DOMContentLoaded', () => {
                attachAuthLinks();
                if ('requestIdleCallback' in window) {
                    requestIdleCallback(preloadCounterpart);
                } else {
                    setTimeout(preloadCounterpart, 200);
                }
            });
        })();
    </script>
</body>
</html>
