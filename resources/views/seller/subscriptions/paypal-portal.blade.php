<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Subscription - PayPal Secured Checkout</title>
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
        <!-- PayPal Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-600/20 text-blue-400 mb-2 border border-blue-500/30">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.78.78 0 0 1 .77-.655h6.91c2.4 0 4.257.545 5.215 1.533.906.934 1.156 2.302.744 4.066-.632 2.709-2.392 4.415-4.832 4.686-.33.037-.665.056-1.004.056H9.72l-1.4 8.283a.64.64 0 0 1-.633.541zM19.78 7.371c-.086.376-.2.745-.342 1.107-.905 2.308-2.973 3.51-6.148 3.51H10.1l-.986 5.836h3.047c.28 0 .524-.2.571-.477l.024-.124.908-5.37.038-.19a.576.576 0 0 1 .57-.478h.42c2.133 0 3.824-.627 4.542-2.317.34-.8.406-1.638.196-2.507z"/>
                </svg>
            </div>
            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>paypal.com · Encrypted & Secure Payment to Admin</span>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight">Openbox Admin Subscription</h1>
            <p class="text-3xl font-extrabold text-white">${{ number_format((float) $subscription->plan->price, 2) }}</p>
            <p class="text-xs text-blue-400 font-medium">{{ $subscription->plan->name }} ({{ ucfirst($subscription->plan->billing_cycle->value) }})</p>
        </div>

        <!-- PayPal Checkout Card -->
        <div class="bg-slate-800/90 backdrop-blur border border-slate-700/60 rounded-2xl p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-700/60 text-xs">
                <span class="text-slate-400">Subscription Plan</span>
                <span class="font-mono text-blue-300 font-semibold">{{ $subscription->plan->name }}</span>
            </div>

            @php
                $confirmRoute = auth()->user()->isBusiness() ? route('business.subscription.paypal-confirm', $subscription) : route('saler.subscriptions.paypal-confirm', $subscription);
                $cancelRoute = auth()->user()->isBusiness() ? route('business.subscription.index') : route('saler.subscriptions.index');
            @endphp

            <div class="space-y-4">
                <div class="bg-slate-900/80 border border-slate-700/80 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Recipient Account:</span>
                        <span class="text-slate-200 font-semibold">Admin PayPal Merchant Gateway</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Payer Account:</span>
                        <span class="text-slate-200 font-semibold">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Total Amount:</span>
                        <span class="text-emerald-400 font-bold">${{ number_format((float) $subscription->plan->price, 2) }} USD</span>
                    </div>
                </div>

                <form method="POST" action="{{ $confirmRoute }}" class="space-y-3">
                    @csrf
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-slate-950" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.78.78 0 0 1 .77-.655h6.91c2.4 0 4.257.545 5.215 1.533.906.934 1.156 2.302.744 4.066-.632 2.709-2.392 4.415-4.832 4.686-.33.037-.665.056-1.004.056H9.72l-1.4 8.283a.64.64 0 0 1-.633.541z"/>
                        </svg>
                        <span>Pay with PayPal Express Checkout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-500 space-y-1">
            <p>Powered by <strong class="text-slate-400">PayPal Secured Gateway</strong> · Terms · Privacy</p>
            <p><a href="{{ $cancelRoute }}" class="underline hover:text-slate-400">Cancel and return to Subscriptions</a></p>
        </div>
    </div>
</body>
</html>
