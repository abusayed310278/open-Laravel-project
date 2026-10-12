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
        class="logoutButton logoutButton--light w-full group relative flex items-center justify-between px-4 py-2.5 bg-white hover:bg-gray-50 border-0 rounded-xl shadow-xs hover:shadow-md transition-all duration-200 cursor-pointer text-left"
        title="{{ $text }}"
    >
        <span class="button-text text-sm font-semibold text-gray-800 group-hover:text-gray-950 transition-colors">
            {{ $text }}
        </span>

        {{-- Animated Door & Walking Figure Graphic --}}
        <div class="door-animation-container relative w-8 h-9 shrink-0 flex items-center justify-center">
            {{-- Doorway Frame with Illuminated Blue Passage --}}
            <svg class="doorway-frame w-8 h-9" viewBox="0 0 32 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                {{-- Outer Door Casing --}}
                <rect x="2" y="2" width="28" height="34" rx="3.5" fill="#1f2335" />
                {{-- Inner Illuminated Blue Passage --}}
                <rect class="doorway-light" x="5" y="5" width="22" height="28" rx="2" fill="#3b82f6" />
                {{-- Floor Threshold --}}
                <line x1="2" y1="34" x2="30" y2="34" stroke="#111827" stroke-width="2" />
            </svg>

            {{-- 3D Hinged Door Panel --}}
            <div class="door-panel">
                <div class="door-handle"></div>
            </div>

            {{-- Walking Silhouette Figure --}}
            <svg class="walking-figure" viewBox="0 0 20 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="10" cy="4.5" r="2.8" fill="#ffffff" />
                <path d="M10 7.5V17" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" />
                <path class="figure-arm left" d="M10 10L6.5 14" stroke="#ffffff" stroke-width="2" stroke-linecap="round" />
                <path class="figure-arm right" d="M10 10L13.5 14" stroke="#ffffff" stroke-width="2" stroke-linecap="round" />
                <path class="figure-leg left" d="M10 17L7 24.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" />
                <path class="figure-leg right" d="M10 17L13 24.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" />
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
        perspective: 120px;
    }

    /* 3D Hinged Door Panel */
    .logoutButton--light .door-panel {
        position: absolute;
        top: 5px;
        left: 5px;
        width: 22px;
        height: 28px;
        background: #2563eb;
        border: none;
        border-radius: 2px;
        transform-origin: left center;
        transform-style: preserve-3d;
        transform: perspective(100px) rotateY(-18deg);
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        z-index: 10;
        box-shadow: 1px 0 4px rgba(0, 0, 0, 0.2);
    }

    .logoutButton--light .door-handle {
        position: absolute;
        top: 50%;
        right: 3px;
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #facc15;
        box-shadow: 0 0 2px rgba(0, 0, 0, 0.4);
    }

    /* Walking Figure */
    .logoutButton--light .walking-figure {
        position: absolute;
        width: 18px;
        height: 25px;
        top: 6px;
        left: 6px;
        z-index: 5;
        transform: translateX(-1px) scale(0.92);
        transition: transform 0.35s ease, opacity 0.35s ease;
        pointer-events: none;
    }

    .logoutButton--light .figure-leg,
    .logoutButton--light .figure-arm {
        transform-origin: 10px 17px;
        transition: transform 0.3s ease;
    }

    /* Hover State: Door opens wider, figure steps into the doorway */
    .logoutButton--light:hover .door-panel {
        transform: perspective(100px) rotateY(-68deg);
    }

    .logoutButton--light:hover .walking-figure {
        transform: translateX(2px) scale(1);
    }

    .logoutButton--light:hover .figure-leg.left {
        transform: rotate(-16deg);
    }

    .logoutButton--light:hover .figure-leg.right {
        transform: rotate(18deg);
    }

    .logoutButton--light:hover .figure-arm.left {
        transform: rotate(20deg);
    }

    .logoutButton--light:hover .figure-arm.right {
        transform: rotate(-22deg);
    }

    /* Click / Logging Out Animation State */
    .logoutButton--light.is-logging-out {
        pointer-events: none;
        background-color: #f8fafc;
        border: none !important;
    }

    .logoutButton--light.is-logging-out .button-text {
        opacity: 0.6;
    }

    /* Figure walks out through doorway and exits */
    .logoutButton--light.is-logging-out .walking-figure {
        animation: figureWalkOut 0.55s forwards cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Door slams shut after figure passes */
    .logoutButton--light.is-logging-out .door-panel {
        animation: doorSlamShut 0.55s forwards cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    @keyframes figureWalkOut {
        0% {
            transform: translateX(2px) scale(1);
            opacity: 1;
        }
        50% {
            transform: translateX(10px) scale(1.05);
            opacity: 0.9;
        }
        100% {
            transform: translateX(24px) scale(0.9);
            opacity: 0;
        }
    }

    @keyframes doorSlamShut {
        0% {
            transform: perspective(100px) rotateY(-68deg);
        }
        40% {
            transform: perspective(100px) rotateY(-75deg);
        }
        75% {
            transform: perspective(100px) rotateY(0deg);
        }
        85% {
            transform: perspective(100px) rotateY(-8deg);
        }
        100% {
            transform: perspective(100px) rotateY(0deg);
        }
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
            }, 520);
        });
    })();
</script>
