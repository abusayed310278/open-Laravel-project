<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Subscription - Stripe Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .card-mono { font-family: 'Share Tech Mono', monospace, monospace; }

        /* 3D Perspective and Card Flip */
        .card-scene {
            perspective: 1200px;
        }
        .credit-card {
            width: 100%;
            max-width: 360px;
            height: 220px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.65s cubic-bezier(0.4, 0.2, 0.2, 1);
        }
        .credit-card.is-flipped {
            transform: rotateY(180deg);
        }
        .card-face {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border-radius: 18px;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 128, 0.28), 0 10px 20px -5px rgba(15, 23, 42, 0.2);
            overflow: hidden;
        }

        /* Front Face Two-Tone Navy Metallic Styling */
        .card-front {
            background: linear-gradient(135deg, #475569 0%, #1e293b 35%, #0f172a 65%, #000080 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 22px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-front::before {
            content: '';
            position: absolute;
            top: -60%;
            left: -40%;
            width: 180%;
            height: 180%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 65%);
            pointer-events: none;
        }

        .card-front::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 55%;
            background: linear-gradient(170deg, transparent 40%, rgba(0, 0, 128, 0.5) 100%);
            pointer-events: none;
        }

        /* Back Face */
        .card-back {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #000080 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: rotateY(180deg);
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* EMV Chip */
        .chip {
            width: 44px;
            height: 34px;
            border-radius: 7px;
            background: linear-gradient(135deg, #ffd700 0%, #d4af37 50%, #b8860b 100%);
            border: 1px solid #c59b27;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.5), 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .chip::before {
            content: '';
            position: absolute;
            inset: 3px;
            border-radius: 4px;
            border: 1px solid rgba(139, 69, 19, 0.5);
            background: linear-gradient(90deg, transparent 45%, rgba(139, 69, 19, 0.4) 45%, rgba(139, 69, 19, 0.4) 55%, transparent 55%),
                        linear-gradient(0deg, transparent 45%, rgba(139, 69, 19, 0.4) 45%, rgba(139, 69, 19, 0.4) 55%, transparent 55%);
        }

        /* Magnetic Stripe */
        .magnetic-stripe {
            width: 100%;
            height: 42px;
            background: #090d16;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.6);
        }

        .signature-strip {
            background: repeating-linear-gradient(45deg, #e2e8f0, #e2e8f0 6px, #cbd5e1 6px, #cbd5e1 12px);
            height: 34px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen antialiased selection:bg-indigo-500 selection:text-white">
    @php
        $confirmRoute = Route::has('subscriptions.stripe-confirm') ? route('subscriptions.stripe-confirm', $subscription) : (auth()->user()->isBusiness() ? route('business.subscription.stripe-confirm', $subscription) : route('saler.subscriptions.stripe-confirm', $subscription));
        $cancelRoute = auth()->user()->isAdmin() ? route('admin.subscriptions.plans.index') : (auth()->user()->isBusiness() ? route('business.subscription.index') : route('saler.subscriptions.index'));
    @endphp

    <div class="min-h-screen grid lg:grid-cols-12">

        <!-- ========================================== -->
        <!-- LEFT COLUMN: Plan Details & LIVE 3D CARD -->
        <!-- ========================================== -->
        <div class="lg:col-span-5 bg-white border-b lg:border-b-0 lg:border-r border-slate-200/80 p-6 sm:p-10 lg:p-12 flex flex-col justify-between">
            <div class="space-y-6">
                <!-- Top Navigation & Badge -->
                <div class="flex items-center justify-between">
                    <a href="{{ $cancelRoute }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors group">
                        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Back</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase bg-slate-900 text-white">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Sandbox
                        </span>
                    </div>
                </div>

                <!-- Plan Reference & Total Price -->
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                        <span>Pay Openbox Admin</span>
                        <span>·</span>
                        <span class="font-mono text-slate-700 font-semibold">Subscription #{{ $subscription->id }}</span>
                    </div>
                    <div class="text-4xl sm:text-5xl font-black text-slate-950 tracking-tight mt-1">
                        ${{ number_format((float) $subscription->plan->price, 2) }}
                    </div>
                </div>

                <!-- Plan Summary -->
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 text-xs space-y-2 text-slate-600">
                    <div class="flex items-center justify-between font-semibold text-slate-800">
                        <span>Plan Summary</span>
                        <span>{{ ucfirst($subscription->plan->billing_cycle->value) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="truncate max-w-[200px]">{{ $subscription->plan->name }}</span>
                        <span class="font-medium text-slate-700">${{ number_format((float) $subscription->plan->price, 2) }}</span>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- THE LIVE 3D INTERACTIVE CARD -->
                <!-- ========================================== -->
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Live Card Preview</span>
                        <span class="text-[11px] text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                            Updates in real time as you type
                        </span>
                    </div>

                    <div class="card-scene flex justify-center">
                        <div class="credit-card" id="credit-card-preview">

                            <!-- CARD FRONT -->
                            <div class="card-face card-front relative select-none">
                                <!-- Top Row: Stripe Logo & Detected Brand -->
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-1 text-white">
                                        <svg class="h-6 w-auto fill-current" viewBox="0 0 60 25">
                                            <path d="M59.64 14.28c0-4.52-2.22-6.66-6.17-6.66-3.95 0-6.49 2.14-6.49 6.63 0 5.25 3.01 6.58 6.94 6.58 1.93 0 3.39-.42 4.49-1.04v-3.07c-1.1.57-2.36.9-3.87.9-1.56 0-2.85-.54-3.04-2.19h8.1c.04-.37.04-.84.04-1.15zm-8.12-1.28c.12-1.51.98-2.14 2.01-2.14 1.01 0 1.86.63 1.99 2.14h-4zm-7.85-6.34c-1.13 0-2.07.54-2.61 1.25V6.9H37v17.44l4.04-.86v-4.57c.56.59 1.45 1.1 2.54 1.1 2.87 0 5.17-2.4 5.17-6.7 0-4.48-2.29-6.55-5.08-6.55zm-1.02 9.9c-.93 0-1.7-.54-2.12-1.35v-4.05c.42-.81 1.19-1.35 2.12-1.35 1.49 0 2.45 1.34 2.45 3.37 0 2.01-.96 3.38-2.45 3.38zM33.6 5.86l4.05-.86V1.75L33.6 2.6v3.26zm0 1.04h4.05v13.06H33.6V6.9zm-4.7 1.06c-.63-.73-1.53-1.3-2.73-1.3-2.6 0-4.66 2.14-4.66 6.53 0 4.45 2.03 6.64 4.7 6.64 1.15 0 2.04-.54 2.65-1.23v1.07h4.04V1.75l-4-.86v6.07zm-1.4 9.1c-1.42 0-2.34-1.31-2.34-3.32 0-2.03.95-3.35 2.37-3.35.88 0 1.63.54 2.05 1.35v4c-.42.8-1.2 1.32-2.08 1.32zm-12.78-2.82c0-3.33 2.6-4.62 6.32-4.62.91 0 1.81.1 2.55.33V8.8c0-1.87-1.36-2.58-3.07-2.58-1.78 0-3.52.66-4.74 1.41L15 4.39C16.65 3.4 19.14 2.8 22.08 2.8c4.63 0 7.37 2.24 7.37 6.66v10.4l-3.95.84v-1.28c-.85.83-2.02 1.44-3.56 1.44-3.6 0-6.22-2.17-6.22-6.62zm8.87-.27v-1.75c-.56-.22-1.33-.35-2.18-.35-1.92 0-3.04.75-3.04 2.19 0 1.38 1.07 2.12 2.36 2.12 1.16 0 2.15-.71 2.86-2.21zm-14.86-3.8c-1.94-.72-3.01-1.28-3.01-2.16 0-.74.61-1.17 1.7-1.17 2 0 4.05.77 5.46 1.47l.8-4.94c-1.57-.75-3.86-1.27-6.39-1.27-4.7 0-7.98 2.48-7.98 6.32 0 4.64 4.22 5.66 7.32 6.8 2.24.82 3.01 1.53 3.01 2.45 0 .94-.85 1.46-2.21 1.46-1.95 0-4.64-.95-6.48-1.98l-.87 5.01c1.92 1.02 4.82 1.67 7.43 1.67 4.99 0 8.5-2.34 8.5-6.58 0-4.44-4.02-5.86-7.78-7.28z"/>
                                        </svg>
                                    </div>
                                    <div id="card-brand-display" class="flex items-center gap-1 font-bold text-xs uppercase tracking-wider text-slate-200">
                                        <span id="card-brand-text">VISA</span>
                                    </div>
                                </div>

                                <!-- Middle Row: Chip & Contactless Wave -->
                                <div class="flex items-center gap-3 relative z-10 my-1">
                                    <div class="chip"></div>
                                    <svg class="w-5 h-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.5 16.5a5 5 0 010-9m3.5 11.5a9 9 0 000-14m3.5 16.5a13 13 0 000-19"/>
                                    </svg>
                                </div>

                                <!-- Card Number Monospace -->
                                <div class="relative z-10">
                                    <div id="card-preview-number" class="card-mono text-lg sm:text-xl font-bold tracking-[0.16em] text-white text-shadow-sm truncate">
                                        •••• &nbsp;•••• &nbsp;•••• &nbsp;••••
                                    </div>
                                </div>

                                <!-- Bottom Row: Cardholder Name & Expiry -->
                                <div class="flex items-end justify-between relative z-10 pt-1">
                                    <div class="min-w-0 pr-3">
                                        <span class="block text-[8px] font-bold uppercase tracking-widest text-slate-400">Cardholder</span>
                                        <span id="card-preview-name" class="block text-xs sm:text-sm font-semibold tracking-wider text-slate-100 uppercase truncate max-w-[200px]">
                                            {{ auth()->user()->name }}
                                        </span>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="block text-[8px] font-bold uppercase tracking-widest text-slate-400">Expires</span>
                                        <span id="card-preview-exp" class="card-mono text-xs sm:text-sm font-semibold tracking-wider text-slate-100">
                                            MM / YY
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD BACK -->
                            <div class="card-face card-back relative select-none">
                                <div class="magnetic-stripe mt-2"></div>

                                <div class="px-6 py-2 space-y-2">
                                    <div class="flex items-center justify-between text-[8px] text-slate-400 uppercase tracking-widest">
                                        <span>Authorized Signature</span>
                                        <span>CVV / CVC</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="signature-strip flex-1"></div>
                                        <div class="bg-white text-slate-900 font-mono font-bold text-xs px-3 py-1.5 rounded text-center tracking-widest shadow-inner min-w-[50px]">
                                            <span id="card-preview-cvc">•••</span>
                                        </div>
                                    </div>
                                    <p class="text-[8px] text-slate-400 leading-tight pt-1">
                                        This card is verified for secure SSL encrypted transactions via Openbox Marketplace payment gateway.
                                    </p>
                                </div>

                                <div class="px-6 pb-2 flex items-center justify-between text-slate-400 text-[10px]">
                                    <span class="font-bold tracking-widest uppercase text-white/70">OPENBOX ROCKET</span>
                                    <span>24/7 Security</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Left Footer Guarantee -->
            <div class="pt-6 border-t border-slate-100 text-[11px] text-slate-400 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Guaranteed 256-bit SSL encrypted & Level 1 PCI-DSS compliant checkout.</span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: Payment Method Form -->
        <!-- ========================================== -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-10 lg:p-12 flex flex-col justify-center max-w-xl mx-auto w-full">
            <div class="w-full space-y-6">

                <!-- Express Checkout Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="quickFillDemo('apple')"
                        class="h-11 rounded-lg bg-black text-white hover:bg-slate-900 transition-all flex items-center justify-center gap-1.5 shadow-xs font-semibold text-sm active:scale-[0.99] cursor-pointer">
                        <svg class="w-4 h-4 fill-current -mt-0.5" viewBox="0 0 170 170">
                            <path d="M150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.19-2.12-9.97-3.17-14.34-3.17-4.58 0-9.49 1.05-14.75 3.17-5.26 2.13-9.5 3.24-12.74 3.35-4.35.13-9.16-1.9-14.42-6.08-3.69-3.06-7.7-7.91-12.04-14.56-6.3-9.61-11.29-20.47-14.97-32.57-3.69-12.1-5.53-23.47-5.53-34.1 0-14.4 3.58-26.33 10.74-35.8 7.16-9.46 16.14-14.28 26.94-14.44 5.37 0 11.13 1.37 17.29 4.11 6.16 2.74 10.15 4.17 11.97 4.29 1.57 0 5.63-1.48 12.18-4.44 6.55-2.96 12.33-4.32 17.34-4.08 13.04.85 23.44 5.58 31.2 14.19-11.45 6.94-17.06 16.51-16.83 28.71.22 9.54 3.91 17.52 11.07 23.94 7.16 6.42 15.68 10.02 25.56 10.8-2.02 6.09-4.5 12.02-7.44 17.79zM119.22 33.15c0-7.39 2.66-14.39 7.98-21 5.32-6.61 11.92-10.71 19.8-12.3 0 1.06.06 2.06.06 3.01 0 7.16-2.82 14.2-8.47 21.11-5.65 6.91-12.15 10.78-19.5 11.61.08-.82.13-1.63.13-2.43z"/>
                        </svg>
                        <span>Pay</span>
                    </button>

                    <button type="button" onclick="quickFillDemo('link')"
                        class="h-11 rounded-lg bg-[#00D66F] text-slate-950 hover:bg-[#00c063] transition-all flex items-center justify-center gap-1.5 shadow-xs font-bold text-sm active:scale-[0.99] cursor-pointer">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M5.5 3h13a2.5 2.5 0 012.5 2.5v13a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 18.5v-13A2.5 2.5 0 015.5 3zm6.5 4l-4 4 4 4 1.4-1.4L10.8 11H17V9h-6.2l2.6-2.6L12 7z"/>
                        </svg>
                        <span>link</span>
                    </button>
                </div>

                <!-- OR Divider -->
                <div class="relative flex items-center py-1">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">OR</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <!-- Form to Confirm Stripe Payment -->
                <form id="stripe-checkout-form" method="POST" action="{{ $confirmRoute }}" class="space-y-4">
                    @csrf

                    <!-- Contact Information -->
                    <div>
                        <label for="stripe_email" class="block text-xs font-bold text-slate-700 mb-1">Contact information</label>
                        <input
                            type="email"
                            id="stripe_email"
                            name="email"
                            value="{{ auth()->user()->email }}"
                            placeholder="email@example.com"
                            required
                            class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-2xs"
                        >
                    </div>

                    <!-- Payment Method Container -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Payment method</label>

                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                            <!-- Card Radio Header -->
                            <div class="bg-slate-50/70 border-b border-slate-200 px-4 py-3 flex items-center justify-between text-xs font-bold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <span>Card</span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-normal">Encrypted</span>
                            </div>

                            <!-- Card Information Unified Input Group -->
                            <div class="p-4 space-y-3 bg-white">
                                <div>
                                    <span class="block text-[11px] font-semibold text-slate-600 mb-1">Card information</span>
                                    <div class="border border-slate-200 rounded-lg overflow-hidden divide-y divide-slate-200 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-transparent transition-all">
                                        <!-- Card Number -->
                                        <div class="relative bg-white">
                                            <input
                                                type="text"
                                                id="card_number_input"
                                                name="card_number"
                                                inputmode="numeric"
                                                autocomplete="cc-number"
                                                maxlength="19"
                                                placeholder="1234 1234 1234 1234"
                                                required
                                                class="w-full bg-transparent px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none pr-28 font-mono"
                                            >
                                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1.5 opacity-90 pointer-events-none">
                                                <span id="brand-badge-visa" class="text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">VISA</span>
                                                <span id="brand-badge-mc" class="text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">MC</span>
                                                <span id="brand-badge-amex" class="text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">AMEX</span>
                                            </div>
                                        </div>

                                        <!-- Expiry & CVC Grid -->
                                        <div class="grid grid-cols-2 divide-x divide-slate-200 bg-white">
                                            <div>
                                                <input
                                                    type="text"
                                                    id="card_exp_input"
                                                    name="card_expiry"
                                                    inputmode="numeric"
                                                    maxlength="5"
                                                    placeholder="MM / YY"
                                                    required
                                                    class="w-full bg-transparent px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none font-mono text-center sm:text-left"
                                                >
                                            </div>
                                            <div class="relative">
                                                <input
                                                    type="text"
                                                    id="card_cvc_input"
                                                    name="card_cvc"
                                                    inputmode="numeric"
                                                    maxlength="4"
                                                    placeholder="CVC"
                                                    required
                                                    class="w-full bg-transparent px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none pr-9 font-mono text-center sm:text-left"
                                                >
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cardholder Name -->
                                <div>
                                    <label for="card_name_input" class="block text-[11px] font-semibold text-slate-600 mb-1">Cardholder name</label>
                                    <input
                                        type="text"
                                        id="card_name_input"
                                        name="cardholder_name"
                                        value="{{ auth()->user()->name }}"
                                        placeholder="Full name on card"
                                        required
                                        class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                    >
                                </div>

                                <!-- Country / Region Dropdown -->
                                <div>
                                    <label for="card_country_select" class="block text-[11px] font-semibold text-slate-600 mb-1">Country or region</label>
                                    <select
                                        id="card_country_select"
                                        name="country"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer"
                                    >
                                        <option value="Bangladesh" selected>Bangladesh</option>
                                        <option value="United States">United States</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="France">France</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="India">India</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save for faster checkout checkbox -->
                    <label class="flex items-start gap-2.5 text-xs text-slate-600 pt-1 cursor-pointer">
                        <input type="checkbox" name="save_info" checked class="w-4 h-4 rounded accent-indigo-600 mt-0.5">
                        <span>
                            <strong class="font-semibold text-slate-800 block">Save my information for faster checkout</strong>
                            <span class="text-[11px] text-slate-500">Pay securely on this site and everywhere Link is accepted.</span>
                        </span>
                    </label>

                    <!-- Big Blue Stripe Pay Button -->
                    <button
                        type="submit"
                        id="stripe_pay_btn"
                        class="w-full bg-[#0070BA] hover:bg-[#005ea6] active:bg-[#004c86] text-white font-bold text-sm py-3.5 rounded-lg shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99] mt-3"
                    >
                        <span class="btn-text">Pay ${{ number_format((float) $subscription->plan->price, 2) }}</span>
                        <span class="btn-loader hidden items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span>Processing Payment...</span>
                        </span>
                    </button>
                </form>

                <!-- Footer Links & Info -->
                <div class="text-center text-xs text-slate-400 space-y-1.5 pt-2 border-t border-slate-100">
                    <p>Powered by <strong class="text-slate-600 font-semibold">Stripe</strong> · <a href="#" class="hover:underline">Terms</a> · <a href="#" class="hover:underline">Privacy</a></p>
                    <p><a href="{{ $cancelRoute }}" class="text-slate-500 hover:text-slate-800 underline transition-colors">Cancel and return to Subscriptions</a></p>
                </div>

            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- JAVASCRIPT: Real-time Live Card Interaction -->
    <!-- ========================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cardElement = document.getElementById('credit-card-preview');
            const numInput = document.getElementById('card_number_input');
            const expInput = document.getElementById('card_exp_input');
            const cvcInput = document.getElementById('card_cvc_input');
            const nameInput = document.getElementById('card_name_input');

            const previewNumber = document.getElementById('card-preview-number');
            const previewExp = document.getElementById('card-preview-exp');
            const previewCvc = document.getElementById('card-preview-cvc');
            const previewName = document.getElementById('card-preview-name');
            const previewBrand = document.getElementById('card-brand-text');

            // Quick demo filler for Apple Pay / Link buttons
            window.quickFillDemo = function(type) {
                if (numInput) {
                    numInput.value = '4242 4242 4242 4242';
                    numInput.dispatchEvent(new Event('input'));
                }
                if (expInput) {
                    expInput.value = '12/28';
                    expInput.dispatchEvent(new Event('input'));
                }
                if (cvcInput) {
                    cvcInput.value = '123';
                    cvcInput.dispatchEvent(new Event('input'));
                }
                if (nameInput && !nameInput.value) {
                    nameInput.value = type === 'apple' ? 'Apple Pay Customer' : 'Link Customer';
                    nameInput.dispatchEvent(new Event('input'));
                }
            };

            // 1. Format and Sync Card Number
            if (numInput && previewNumber) {
                numInput.addEventListener('input', function(e) {
                    let val = e.target.value.replace(/\D/g, '').substring(0, 16);
                    let formatted = val.match(/.{1,4}/g)?.join(' ') || '';
                    e.target.value = formatted;

                    if (val.length === 0) {
                        previewNumber.innerHTML = '•••• &nbsp;•••• &nbsp;•••• &nbsp;••••';
                    } else {
                        let padded = val.padEnd(16, '•');
                        let chunks = padded.match(/.{1,4}/g) || [];
                        previewNumber.textContent = chunks.join('   ');
                    }

                    detectCardBrand(val);
                });
            }

            function detectCardBrand(digits) {
                const visaBadge = document.getElementById('brand-badge-visa');
                const mcBadge = document.getElementById('brand-badge-mc');
                const amexBadge = document.getElementById('brand-badge-amex');

                [visaBadge, mcBadge, amexBadge].forEach(b => {
                    if (b) b.className = 'text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200';
                });

                if (/^4/.test(digits)) {
                    if (previewBrand) previewBrand.textContent = 'VISA';
                    if (visaBadge) visaBadge.className = 'text-[10px] font-bold text-white bg-blue-600 px-1.5 py-0.5 rounded border border-blue-700';
                } else if (/^(5[1-5]|2[2-7])/.test(digits)) {
                    if (previewBrand) previewBrand.textContent = 'MASTERCARD';
                    if (mcBadge) mcBadge.className = 'text-[10px] font-bold text-white bg-amber-600 px-1.5 py-0.5 rounded border border-amber-700';
                } else if (/^3[47]/.test(digits)) {
                    if (previewBrand) previewBrand.textContent = 'AMEX';
                    if (amexBadge) amexBadge.className = 'text-[10px] font-bold text-white bg-emerald-600 px-1.5 py-0.5 rounded border border-emerald-700';
                } else if (/^6(?:011|5)/.test(digits)) {
                    if (previewBrand) previewBrand.textContent = 'DISCOVER';
                } else {
                    if (previewBrand) previewBrand.textContent = 'VISA';
                }
            }

            // 2. Format and Sync Expiry Date (MM / YY)
            if (expInput && previewExp) {
                expInput.addEventListener('input', function(e) {
                    let val = e.target.value.replace(/\D/g, '').substring(0, 4);
                    if (val.length >= 2) {
                        e.target.value = val.substring(0, 2) + '/' + val.substring(2);
                    } else {
                        e.target.value = val;
                    }

                    if (val.length === 0) {
                        previewExp.textContent = 'MM / YY';
                    } else {
                        let m = val.substring(0, 2).padEnd(2, '•');
                        let y = val.substring(2).padEnd(2, '•');
                        previewExp.textContent = `${m} / ${y}`;
                    }
                });
            }

            // 3. Sync Cardholder Name
            if (nameInput && previewName) {
                const defaultName = previewName.textContent.trim() || 'JANE D. ROCKET';
                nameInput.addEventListener('input', function(e) {
                    let val = e.target.value.trim();
                    previewName.textContent = val.length > 0 ? val.toUpperCase() : defaultName;
                });
            }

            // 4. Sync CVC and Auto 3D Flip on Focus
            if (cvcInput && previewCvc && cardElement) {
                cvcInput.addEventListener('focus', function() {
                    cardElement.classList.add('is-flipped');
                });

                cvcInput.addEventListener('blur', function() {
                    cardElement.classList.remove('is-flipped');
                });

                cvcInput.addEventListener('input', function(e) {
                    let val = e.target.value.replace(/\D/g, '').substring(0, 4);
                    e.target.value = val;
                    previewCvc.textContent = val.length > 0 ? val : '•••';
                });
            }

            // 5. Submit Button Loading Animation
            const form = document.getElementById('stripe-checkout-form');
            const submitBtn = document.getElementById('stripe_pay_btn');
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    const textSpan = submitBtn.querySelector('.btn-text');
                    const loaderSpan = submitBtn.querySelector('.btn-loader');
                    submitBtn.disabled = true;
                    if (textSpan) textSpan.classList.add('hidden');
                    if (loaderSpan) {
                        loaderSpan.classList.remove('hidden');
                        loaderSpan.classList.add('inline-flex');
                    }
                });
            }
        });
    </script>
</body>
</html>
