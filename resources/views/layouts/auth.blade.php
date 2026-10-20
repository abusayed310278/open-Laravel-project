<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    <style>
        @keyframes authFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.99);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .animate-auth-card {
            animation: authFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .auth-card-exit {
            opacity: 0 !important;
            transform: translateY(-8px) scale(0.985) !important;
            transition: opacity 0.18s cubic-bezier(0.4, 0, 0.2, 1), transform 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased selection:bg-brand-500 selection:text-white">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <a href="{{ route('home') }}" class="mb-8 transition-transform duration-200 hover:scale-105 active:scale-95">
            <x-brand-logo size="lg" />
        </a>

        <div id="auth-card" class="w-full {{ $maxWidth ?? 'max-w-md' }} bg-white border border-gray-100 rounded-md p-8 shadow-xs animate-auth-card">
            @yield('content')
        </div>
    </div>

    <script>
        // Smooth transition when navigating between auth pages (login <-> register)
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('auth-card');
            document.querySelectorAll('a[href*="/login"], a[href*="/register"]').forEach((link) => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || link.target === '_blank') return;
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                    
                    e.preventDefault();
                    if (card) {
                        card.classList.add('auth-card-exit');
                    }
                    setTimeout(() => {
                        window.location.href = href;
                    }, 170);
                });
            });
        });
    </script>
</body>
</html>
