<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Deposit Instructions - Subscription</title>
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
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-600/20 text-emerald-400 mb-2 border border-emerald-500/30">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight">Admin Bank Transfer</h1>
            <p class="text-3xl font-extrabold text-white">${{ number_format((float) $subscription->plan->price, 2) }}</p>
            <p class="text-xs text-emerald-400 font-medium">{{ $subscription->plan->name }} ({{ ucfirst($subscription->plan->billing_cycle->value) }})</p>
        </div>

        <!-- Bank Details Card -->
        <div class="bg-slate-800/90 backdrop-blur border border-slate-700/60 rounded-2xl p-6 shadow-2xl space-y-5">
            <div class="space-y-3">
                <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Official Admin Bank Details</h2>
                <div class="bg-slate-900/80 border border-slate-700/80 rounded-xl p-4 text-xs font-mono whitespace-pre-line text-slate-200 leading-relaxed">
                    {{ $bankDetails ?: "Bank Name: Openbox Treasury Bank\nAccount Name: Openbox Admin Inc.\nAccount No: 1234-5678-9012\nSWIFT / IBAN: OPBXUS33XXX" }}
                </div>
            </div>

            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3.5 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-xs text-amber-200">
                    <p class="font-semibold">Pending Admin Verification</p>
                    <p class="mt-0.5 opacity-90">Please transfer the plan amount to the bank account above and include your reference code. Admin will verify your transfer and activate your subscription.</p>
                </div>
            </div>

            @php
                $returnRoute = auth()->user()->isBusiness() ? route('business.subscription.index') : route('saler.subscriptions.index');
            @endphp

            <a href="{{ $returnRoute }}" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-semibold text-xs py-3 rounded-xl transition-all block text-center">
                Return to Subscriptions Management
            </a>
        </div>
    </div>
</body>
</html>
