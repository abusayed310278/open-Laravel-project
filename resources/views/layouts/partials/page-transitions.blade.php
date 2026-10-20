    <style>
        @keyframes admin-page-in {
            from { opacity: 0; transform: translate3d(0, 18px, 0); }
            to { opacity: 1; transform: translate3d(0, 0, 0); }
        }
        @keyframes admin-topbar-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes admin-progress {
            0% { transform: scaleX(0); opacity: 1; }
            80% { transform: scaleX(0.85); opacity: 1; }
            100% { transform: scaleX(0.95); opacity: 1; }
        }
        @view-transition { navigation: auto; }
        #sidebar { view-transition-name: admin-sidebar; }
        .main-content-wrapper > :first-child { view-transition-name: admin-topbar; }
        #admin-main { view-transition-name: admin-content; }
        .main-content-wrapper > footer { view-transition-name: admin-footer; }
        ::view-transition-group(*) { animation-duration: 0.4s; animation-timing-function: cubic-bezier(0.22, 1, 0.36, 1); }
        ::view-transition-old(admin-content) { animation: admin-vt-out 0.22s cubic-bezier(0.4, 0, 1, 1) both; }
        ::view-transition-new(admin-content) { animation: admin-vt-in 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both; }
        @keyframes admin-vt-out { to { opacity: 0; transform: translate3d(0, -10px, 0); } }
        @keyframes admin-vt-in { from { opacity: 0; transform: translate3d(0, 14px, 0); } }
        #admin-main > *:not(.dash-reveal) {
            animation: admin-page-in 0.7s cubic-bezier(0.16, 1, 0.3, 1) backwards;
            animation-delay: calc(var(--p, 0) * 80ms);
        }
        #admin-main {
            transition: opacity 0.18s ease, transform 0.18s ease;
        }
        #admin-main.page-leaving {
            opacity: 0;
            transform: translateY(-8px);
            pointer-events: none;
        }
        #admin-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            z-index: 100;
            background: linear-gradient(90deg, var(--color-brand-400, #9ca3af), var(--color-brand-600, #111827));
            transform: scaleX(0);
            transform-origin: left;
            opacity: 0;
            pointer-events: none;
        }
        #admin-progress.active {
            animation: admin-progress 1.2s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .main-content-wrapper > header,
        .main-content-wrapper > div:first-child {
            animation: admin-topbar-in 0.5s ease backwards;
        }
        @media (prefers-reduced-motion: reduce) {
            #admin-main > *:not(.dash-reveal) { animation: none; }
            #admin-main { transition: none; }
            #admin-progress.active, .main-content-wrapper > header { animation: none; }
        }
    </style>
    <div id="admin-progress"></div>

    {{-- Smooth page transitions between admin pages --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const main = document.getElementById('admin-main');
            const progress = document.getElementById('admin-progress');
            if (!main) return;

            Array.from(main.children).forEach(function(child, index) {
                child.style.setProperty('--p', Math.min(index, 8));
            });

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            // Browsers with cross-document View Transitions animate natively; skip the manual fallback.
            if ('CSSViewTransitionRule' in window) return;

            window.addEventListener('pageshow', function() {
                main.classList.remove('page-leaving');
                progress.classList.remove('active');
            });

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                if (link.target === '_blank' || link.hasAttribute('download')) return;
                if (link.closest('#search-results-wrapper, #pages-results-wrapper, [data-search-results] nav')) return;

                const url = new URL(link.href, window.location.href);
                if (url.origin !== window.location.origin) return;
                if (url.pathname === window.location.pathname && url.search === window.location.search) return;

                e.preventDefault();
                main.classList.add('page-leaving');
                progress.classList.add('active');
                setTimeout(function() { window.location.href = url.href; }, 180);
            });
        });
    </script>
