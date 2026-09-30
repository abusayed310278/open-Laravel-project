<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Openbox Marketplace - Stripe Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full mx-auto space-y-6">
        <!-- Stripe Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-indigo-600/20 text-indigo-400 mb-2 border border-indigo-500/30">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697.4 12.876.4 7.625.4 3.97 3.16 3.97 7.428c0 5.166 4.707 6.304 8.163 7.573 2.502.916 3.356 1.706 3.356 2.73 0 1.053-.942 1.631-2.464 1.631-2.172 0-5.18-1.053-7.23-2.199l-.97 5.577c2.144 1.136 5.374 1.859 8.283 1.859 5.567 0 9.472-2.603 9.472-7.318 0-4.945-4.485-6.523-8.604-8.131z"/>
                </svg>
            </div>
            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>pay.stripe.com · Encrypted & Secure</span>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight">Openbox Marketplace</h1>
            <p class="text-3xl font-extrabold text-white">${{ number_format($order->total, 2) }}</p>
        </div>

        <!-- Stripe Checkout Card -->
        <div class="bg-slate-800/90 backdrop-blur border border-slate-700/60 rounded-2xl p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-700/60 text-xs">
                <span class="text-slate-400">Order Reference</span>
                <span class="font-mono text-indigo-300 font-semibold">{{ $order->order_number }}</span>
            </div>

            <form method="POST" action="{{ route('checkout.stripe-confirm', $order) . (request()->has('token') ? '?token=' . request()->query('token') : '') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Email</label>
                    <input type="email" value="{{ $order->customer?->email ?? auth()->user()?->email ?? '' }}" readonly class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Card Information</label>
                    <div class="bg-slate-900/80 border border-slate-700 rounded-lg overflow-hidden divide-y divide-slate-700">
                        <div class="relative">
                            <input type="text" placeholder="4242 4242 4242 4242" required class="w-full bg-transparent px-3 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none pr-16">
                            <div class="absolute right-3 top-2.5 flex items-center gap-1 opacity-75">
                                <span class="text-[9px] font-bold text-slate-400 bg-slate-800 px-1 py-0.5 rounded border border-slate-700">VISA</span>
                                <span class="text-[9px] font-bold text-slate-400 bg-slate-800 px-1 py-0.5 rounded border border-slate-700">MC</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2">
                            <input type="text" placeholder="MM / YY" required class="bg-transparent px-3 py-2.5 text-xs text-white placeholder-slate-500 border-r border-slate-700 focus:outline-none">
                            <input type="text" placeholder="CVC" required class="bg-transparent px-3 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Name on Card</label>
                    <input type="text" value="{{ $order->shippingAddress?->name ?? $order->customer?->name ?? auth()->user()?->name ?? 'Customer' }}" required class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Country / Region</label>
                    <select class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2.5 text-xs text-slate-300 focus:outline-none">
                        <option>United States</option>
                        <option>Canada</option>
                        <option>United Kingdom</option>
                        <option>Australia</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-semibold text-sm py-3 rounded-lg shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 mt-2">
                    <svg class="w-4 h-4 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Pay ${{ number_format($order->total, 2) }}</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-500 space-y-1">
            <p>Powered by <strong class="text-slate-400">Stripe</strong> · Terms · Privacy</p>
            <p><a href="{{ route('checkout') }}" class="underline hover:text-slate-400">Cancel and return to Openbox Marketplace</a></p>
        </div>
    </div>
</body>
</html>
