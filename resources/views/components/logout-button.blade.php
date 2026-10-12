@props([
    'class' => '',
    'text' => 'Log Out',
    'id' => null,
])

@php
    $buttonId = $id ?? 'logout-btn-' . uniqid();
@endphp

<form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="m-0 p-0 block w-full js-logout-form {{ $class }}">
    @csrf
    <button
        type="button"
        id="{{ $buttonId }}"
        class="logoutButton logoutButton--light w-full group relative flex items-center justify-between px-4 py-2.5 bg-white hover:bg-brand-50 border-0 rounded-xl shadow-xs hover:shadow-md transition-all duration-200 cursor-pointer text-left"
        title="{{ $text }}"
    >
        <span class="button-text text-sm font-semibold text-gray-800 group-hover:text-gray-950 transition-colors">
            {{ $text }}
        </span>

        {{-- Animated Door & Running Figure Graphic --}}
        <div class="door-animation-container relative w-12 h-9 shrink-0">
            <svg class="doorway-frame absolute right-0 top-0 w-[22px] h-9" viewBox="0 0 22 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="2" width="20" height="33" rx="3" style="fill: var(--color-brand-900, #78350f)" />
                <rect x="3.5" y="4.5" width="15" height="28" rx="1.5" style="fill: var(--color-brand-300, #fcd34d)" />
            </svg>

            <div class="door-panel"><div class="door-handle"></div></div>

            <svg class="running-figure" viewBox="0 0 20 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="11" cy="4.5" r="3" fill="currentColor" />
                <path d="M11 8V17" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                <path class="figure-arm left" d="M11 10L7 14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                <path class="figure-arm right" d="M11 10L15 14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                <path class="figure-leg left" d="M11 17L7 25" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                <path class="figure-leg right" d="M11 17L15 25" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
            </svg>
        </div>
    </button>
</form>

<style>
    .logoutButton--light {
        position: relative;
        overflow: hidden;
        user-select: none;
        border: none !important;
        outline: none !important;
    }

    .logoutButton--light .door-animation-container {
        perspective: 140px;
        color: var(--color-brand-600, #d97706);
    }

    /* Hinged door panel (hinge on its left edge) */
    .logoutButton--light .door-panel {
        position: absolute;
        right: 2.5px;
        top: 4.5px;
        width: 15px;
        height: 28px;
        border-radius: 1.5px;
        background: var(--color-brand-600, #d97706);
        transform-origin: left center;
        transition: transform 0.4s cubic-bezier(0.34, 1.4, 0.64, 1);
        z-index: 3;
    }

    .logoutButton--light .door-handle {
        position: absolute;
        top: 50%;
        right: 2.5px;
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: var(--color-brand-100, #fef3c7);
    }

    /* Figure: hidden at rest, appears left of the door on hover */
    .logoutButton--light .running-figure {
        position: absolute;
        left: 0;
        top: 5px;
        width: 18px;
        height: 25px;
        opacity: 0;
        transform: translateX(-8px);
        transition: transform 0.45s ease, opacity 0.25s ease;
        pointer-events: none;
        z-index: 2;
    }

    .logoutButton--light .figure-leg,
    .logoutButton--light .figure-arm {
        transform-origin: 11px 17px;
    }

    .logoutButton--light .figure-arm {
        transform-origin: 11px 10px;
    }

    .logoutButton--light:hover .door-panel {
        transform: rotateY(-70deg);
    }

    .logoutButton--light:hover .running-figure {
        opacity: 1;
        transform: translateX(6px);
    }

    .logoutButton--light:hover .figure-leg.left { animation: legSwingA 0.4s ease-in-out infinite alternate; }
    .logoutButton--light:hover .figure-leg.right { animation: legSwingB 0.4s ease-in-out infinite alternate; }
    .logoutButton--light:hover .figure-arm.left { animation: legSwingB 0.4s ease-in-out infinite alternate; }
    .logoutButton--light:hover .figure-arm.right { animation: legSwingA 0.4s ease-in-out infinite alternate; }

    @keyframes legSwingA { from { transform: rotate(-28deg); } to { transform: rotate(24deg); } }
    @keyframes legSwingB { from { transform: rotate(24deg); } to { transform: rotate(-28deg); } }

    /* Click: figure sprints through the door, door slams shut */
    .logoutButton--light.is-logging-out {
        pointer-events: none;
        background-color: var(--color-brand-50, #fffbeb);
    }

    .logoutButton--light.is-logging-out .button-text {
        opacity: 0.6;
    }

    .logoutButton--light.is-logging-out .running-figure {
        animation: figureRunIn 0.45s forwards ease-in;
    }

    .logoutButton--light.is-logging-out .door-panel {
        transform: rotateY(-70deg);
        animation: doorSlamShut 0.3s 0.4s forwards ease-in;
    }

    @keyframes figureRunIn {
        0%   { opacity: 1; transform: translateX(6px) scale(1); }
        100% { opacity: 0; transform: translateX(28px) scale(0.7); }
    }

    @keyframes doorSlamShut {
        from { transform: rotateY(-70deg); }
        to   { transform: rotateY(0deg); }
    }
</style>

<script>
    (function() {
        const btn = document.getElementById('{{ $buttonId }}');
        if (!btn || btn.dataset.bound) return;
        btn.dataset.bound = 'true';

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = btn.closest('form');
            if (!form || btn.classList.contains('is-logging-out')) return;

            btn.classList.add('is-logging-out');

            // Wait for figure walk-out & door-slam animation before submitting form
            setTimeout(function() {
                form.submit();
            }, 720);
        });
    })();
</script>
