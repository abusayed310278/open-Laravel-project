<style>
    @keyframes fx-rise {
        0% { opacity: 0; transform: translateY(26px) scale(0.97); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes fx-row-in {
        from { opacity: 0; transform: translateX(-16px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fx-sheen {
        from { transform: translateX(-120%) skewX(-18deg); }
        to { transform: translateX(260%) skewX(-18deg); }
    }
    .fx-reveal {
        animation: fx-rise 0.85s cubic-bezier(0.16, 1, 0.3, 1) backwards;
        animation-delay: calc(var(--fx-i, 0) * 80ms);
        animation-play-state: paused;
        will-change: transform, opacity;
    }
    .fx-reveal.fx-in { animation-play-state: running; }
    .fx-tilt {
        position: relative;
        overflow: hidden;
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease, border-color 0.35s ease;
    }
    .fx-tilt::after {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 40%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.55), transparent);
        transform: translateX(-120%) skewX(-18deg);
        pointer-events: none;
    }
    .fx-tilt:hover { box-shadow: 0 18px 36px -16px rgba(17, 24, 39, 0.35); }
    .fx-tilt:hover::after { animation: fx-sheen 0.9s ease; }
    .fx-row {
        animation: fx-row-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
        animation-delay: calc(var(--fx-r, 0) * 60ms + 350ms);
        transition: background-color 0.25s ease, box-shadow 0.25s ease;
    }
    .fx-row:hover { box-shadow: inset 3px 0 0 #111827; }
    .fx-draw { transition: stroke-dashoffset 1.6s cubic-bezier(0.22, 1, 0.36, 1) 0.2s; }
    .fx-dash { transition: stroke-dasharray 1.4s cubic-bezier(0.22, 1, 0.36, 1) 0.2s; }
    .fx-bar { transition: width 1.3s cubic-bezier(0.22, 1, 0.36, 1) 0.2s; }
    #admin-main a[class*="rounded"][class*="px-"],
    #admin-main button[class*="rounded"][class*="px-"] {
        transition-property: transform, background-color, border-color, color, box-shadow, opacity;
        transition-duration: 0.22s;
        transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
    }
    #admin-main a[class*="rounded"][class*="px-"]:hover,
    #admin-main button[class*="rounded"][class*="px-"]:hover { transform: translateY(-2px); }
    #admin-main a[class*="rounded"][class*="px-"]:active,
    #admin-main button[class*="rounded"][class*="px-"]:active { transform: translateY(0) scale(0.96); }
    @media (prefers-reduced-motion: reduce) {
        .fx-reveal, .fx-row { animation: none; }
        .fx-tilt, .fx-draw, .fx-dash, .fx-bar { transition: none; }
        .fx-tilt:hover::after { animation: none; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const main = document.getElementById('admin-main');
        if (!main) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const isCard = (el) => ['DIV', 'A', 'ASIDE', 'SECTION', 'ARTICLE'].includes(el.tagName)
            && el.offsetHeight > 48 && el.offsetWidth > 100
            && !el.querySelector('input, select, textarea');

        const candidates = Array.from(main.querySelectorAll('[class*="rounded-"][class*="border"]'))
            .filter((el) => el.parentElement !== main && !el.closest('table, form') && isCard(el));
        const outer = candidates.filter((el) => !candidates.some((other) => other !== el && other.contains(el)));

        const numberPattern = /^(\s*[^\d\s]{0,3}\s?)(\d[\d,]*)(\.\d+)?(\s?%?\s*)$/;

        function runCount(el) {
            const original = el.dataset.fxOriginal;
            const match = original.match(numberPattern);
            const target = parseFloat((match[2] + (match[3] || '')).replace(/,/g, ''));
            const decimals = match[3] ? match[3].length - 1 : 0;
            const start = performance.now();
            const duration = 1800;
            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const value = (target * eased).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
                el.textContent = progress < 1 ? match[1] + value + match[4] : original;
                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            };
            requestAnimationFrame(tick);
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                const el = entry.target;
                observer.unobserve(el);
                el.classList.add('fx-in');
                el.querySelectorAll('[data-fx-count]').forEach(runCount);
                el.querySelectorAll('.fx-draw').forEach((node) => { node.style.strokeDashoffset = '0'; });
                el.querySelectorAll('.fx-dash').forEach((node) => { node.style.strokeDasharray = node.dataset.fxDash; });
                el.querySelectorAll('.fx-bar').forEach((node) => { node.style.width = node.dataset.fxWidth; });
            });
        }, { threshold: 0.1 });

        const revealHost = (host) => {
            if (!host.classList.contains('fx-reveal')) {
                host.classList.add('fx-reveal');
                observer.observe(host);
            }
        };

        outer.forEach((el, index) => {
            el.classList.add('fx-reveal');
            el.style.setProperty('--fx-i', Math.min(index, 8));
            observer.observe(el);
        });

        main.querySelectorAll('p, span, div, h2, h3, dd').forEach((el) => {
            if (el.children.length || el.closest('table')) {
                return;
            }
            const text = el.textContent;
            if (!numberPattern.test(text) || parseFloat(getComputedStyle(el).fontSize) < 18) {
                return;
            }
            el.dataset.fxCount = '1';
            el.dataset.fxOriginal = text;
            revealHost(el.closest('.fx-reveal') || el.parentElement);
        });

        main.querySelectorAll('tbody').forEach((body) => {
            body.querySelectorAll('tr').forEach((row, index) => {
                row.classList.add('fx-row');
                row.style.setProperty('--fx-r', Math.min(index, 12));
            });
        });

        main.querySelectorAll('svg polyline').forEach((line) => {
            if (line.getAttribute('fill') !== 'none') {
                return;
            }
            line.setAttribute('pathLength', '1');
            line.style.strokeDasharray = '1';
            line.style.strokeDashoffset = '1';
            line.classList.add('fx-draw');
            revealHost(line.closest('.fx-reveal') || line.closest('svg').parentElement);
        });

        main.querySelectorAll('svg circle[stroke-dasharray]').forEach((circle) => {
            circle.dataset.fxDash = circle.getAttribute('stroke-dasharray');
            circle.style.strokeDasharray = '0 1000';
            circle.classList.add('fx-dash');
            revealHost(circle.closest('.fx-reveal') || circle.closest('svg').parentElement);
        });

        main.querySelectorAll('[style*="width:"][style*="%"]').forEach((bar) => {
            const parent = bar.parentElement;
            if (!parent || !/overflow-hidden/.test(parent.className) || !/rounded/.test(bar.className)) {
                return;
            }
            bar.dataset.fxWidth = bar.style.width;
            bar.style.width = '0%';
            bar.classList.add('fx-bar');
            revealHost(bar.closest('.fx-reveal') || parent);
        });

        if (window.matchMedia('(hover: hover)').matches) {
            main.querySelectorAll('[class*="rounded-"][class*="border"]').forEach((card) => {
                if (!isCard(card) || card.offsetWidth > 440 || card.offsetHeight > 220 || card.closest('table, form')) {
                    return;
                }
                card.classList.add('fx-tilt');
                card.addEventListener('mousemove', (event) => {
                    const rect = card.getBoundingClientRect();
                    const x = (event.clientX - rect.left) / rect.width - 0.5;
                    const y = (event.clientY - rect.top) / rect.height - 0.5;
                    card.style.transform = 'perspective(700px) rotateX(' + (-y * 6).toFixed(2) + 'deg) rotateY(' + (x * 6).toFixed(2) + 'deg) translateY(-4px)';
                });
                card.addEventListener('mouseleave', () => { card.style.transform = ''; });
            });
        }
    });
</script>
