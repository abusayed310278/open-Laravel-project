@extends('layouts.verifier')

@section('title', 'Product Inspection & Certification')

@section('content')
    <div class="mb-1">
        <x-breadcrumb :items="['Inspection Queue' => route('verifier.appointments.index'), $verification->product->title => null]" />
    </div>

    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-950 via-gray-900 to-gray-800 p-5 sm:p-6 text-white shadow-lg">
        <div class="absolute -top-12 -right-12 w-56 h-56 bg-brand-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 left-1/3 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-brand-400 uppercase tracking-wider">Physical Inspection Session</p>
                    <h1 class="text-lg sm:text-xl font-bold text-white truncate">{{ $verification->product->title }}</h1>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $verification->location?->name ?? 'Default Hub' }}
                        @if ($verification->scheduled_at)
                            · Slot: {{ $verification->scheduled_at->format('M j, g:i A') }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <x-badge :color="$verification->status->badgeColor()" class="bg-white/10! text-white! ring-1 ring-white/15">
                    {{ $verification->status->label() }}
                </x-badge>
                <a href="{{ route('verifier.appointments.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold rounded-xl border border-white/10 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <span>Queue</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mt-6">
        {{-- Left: Product Profile & Seller Info --}}
        <div class="lg:col-span-1 space-y-5 lg:sticky lg:top-6 self-start">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-2xs overflow-hidden">
                <div class="relative bg-gray-50">
                    <img src="{{ $verification->product->primaryImageUrl() }}" alt="{{ $verification->product->title }}" class="w-full h-48 object-cover">
                    <div class="absolute top-3 left-3 flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-white/90 backdrop-blur-xs text-gray-800 shadow-2xs">
                            {{ $verification->product->category?->name ?? 'Electronics' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-brand-500/90 backdrop-blur-xs text-white shadow-2xs">
                            {{ $verification->product->condition?->label() ?? 'Pre-owned' }}
                        </span>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <h2 class="font-bold text-base text-gray-950 mb-3">{{ $verification->product->title }}</h2>

                    <dl class="space-y-2.5 text-xs text-gray-600 divide-y divide-gray-50">
                        <div class="flex justify-between items-center pt-2.5 first:pt-0">
                            <dt class="text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Seller
                            </dt>
                            <dd class="font-semibold text-gray-900 flex items-center gap-1.5">
                                <span>{{ $verification->seller->name }}</span>
                                <form method="POST" action="{{ route('chat.start', $verification->product) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="p-1 text-gray-400 hover:text-brand-600 hover:bg-brand-50 rounded transition cursor-pointer" title="Chat with {{ $verification->seller->name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </button>
                                </form>
                            </dd>
                        </div>
                        <div class="flex justify-between pt-2.5"><dt class="text-gray-400">Seller Email</dt><dd class="font-medium text-gray-900 truncate max-w-[160px]" title="{{ $verification->seller->email }}">{{ $verification->seller->email }}</dd></div>
                        <div class="flex justify-between pt-2.5"><dt class="text-gray-400">SKU / Code</dt><dd class="font-mono text-gray-800">{{ $verification->product->sku ?? '—' }}</dd></div>
                        <div class="flex justify-between pt-2.5"><dt class="text-gray-400">Scheduled</dt><dd class="font-medium text-gray-800">{{ $verification->scheduled_at?->format('M j, Y g:i A') ?? 'Pending' }}</dd></div>
                        <div class="flex justify-between pt-2.5"><dt class="text-gray-400">Hub</dt><dd class="font-medium text-gray-800">{{ $verification->location?->name ?? 'Default Hub' }}</dd></div>
                    </dl>

                    <form method="POST" action="{{ route('chat.start', $verification->product) }}" class="mt-4 pt-4 border-t border-gray-100">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-gray-50 hover:bg-brand-50 hover:border-brand-200 border border-gray-200 text-gray-800 hover:text-brand-800 rounded-xl text-xs font-semibold transition cursor-pointer">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Message Seller</span>
                        </button>
                    </form>

                    @if ($verification->product->attributeValues->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Specifications</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($verification->product->attributeValues as $val)
                                    <div class="bg-gray-50 rounded-lg px-2.5 py-2">
                                        <p class="text-[10px] text-gray-400">{{ $val->attribute?->name ?? 'Spec' }}</p>
                                        <p class="text-xs font-semibold text-gray-800 truncate">{{ $val->displayValue() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Grade Guidelines Helper Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-2xs p-4 sm:p-5">
                <p class="font-bold text-gray-900 flex items-center gap-1.5 mb-3 text-sm">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Openbox Grading Standard
                </p>
                <div class="space-y-2 text-xs">
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-100">
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 shrink-0">A</span>
                        <p class="text-gray-600"><strong class="text-gray-900">Like New:</strong> Flawless cosmetic state, no scratches, battery ≥ 85%.</p>
                    </div>
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-blue-50/60 border border-blue-100">
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 shrink-0">B</span>
                        <p class="text-gray-600"><strong class="text-gray-900">Good:</strong> Minor micro-scratches on casing, screen intact, battery ≥ 80%.</p>
                    </div>
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-amber-50/60 border border-amber-100">
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 shrink-0">C</span>
                        <p class="text-gray-600"><strong class="text-gray-900">Fair:</strong> Visible signs of usage/scuffs, 100% functional parts.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Inspection Form & Checklist --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('verifier.appointments.submit', $verification) }}" id="inspection-form" class="space-y-5">
                @csrf

                {{-- Checklist --}}
                <div class="bg-white border border-gray-100 rounded-2xl shadow-2xs p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-bold text-gray-900">Multi-Point Diagnostic Check</p>
                            <p class="text-xs text-gray-400 mt-0.5">Verify each component before final certification.</p>
                        </div>
                        @if ($checklist->isNotEmpty())
                            <div class="flex items-center gap-2 shrink-0">
                                <span id="checklist-progress-label" class="text-xs font-bold text-gray-700">0 / {{ $checklist->count() }} Fail-Flagged</span>
                            </div>
                        @endif
                    </div>

                    @if ($checklist->isEmpty())
                        <div class="p-4 bg-gray-50 rounded-xl text-xs text-gray-500 flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            General electronics inspection: Test power, screen, sensors, ports, and battery before grading.
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($checklist as $item)
                                <div class="checklist-row flex flex-col sm:flex-row sm:items-center justify-between gap-3 border border-gray-100 rounded-xl p-3.5 hover:border-gray-200 hover:bg-gray-50/40 transition-colors bg-white">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <span class="mt-0.5 w-5 h-5 rounded-full bg-gray-100 text-gray-500 text-[10px] font-bold flex items-center justify-center shrink-0">{{ $loop->iteration }}</span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900">{{ $item->item_name }}</p>
                                            @if ($item->description)
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="inline-flex items-center gap-1 p-1 bg-gray-50 rounded-lg border border-gray-100 self-start sm:self-auto shrink-0">
                                        <label class="flex items-center gap-1 px-2.5 py-1.5 rounded-md cursor-pointer text-xs font-semibold text-emerald-700 has-checked:bg-emerald-100 has-checked:shadow-2xs transition-colors">
                                            <input type="radio" name="results[{{ $item->id }}]" value="pass" class="accent-emerald-600 checklist-input" data-item="{{ $item->id }}" checked>
                                            <span>Pass</span>
                                        </label>
                                        <label class="flex items-center gap-1 px-2.5 py-1.5 rounded-md cursor-pointer text-xs font-semibold text-red-700 has-checked:bg-red-100 has-checked:shadow-2xs transition-colors">
                                            <input type="radio" name="results[{{ $item->id }}]" value="fail" class="accent-red-600 checklist-input" data-item="{{ $item->id }}">
                                            <span>Fail</span>
                                        </label>
                                        <label class="flex items-center gap-1 px-2.5 py-1.5 rounded-md cursor-pointer text-xs font-semibold text-gray-600 has-checked:bg-gray-200 transition-colors">
                                            <input type="radio" name="results[{{ $item->id }}]" value="na" class="accent-gray-600 checklist-input" data-item="{{ $item->id }}">
                                            <span>N/A</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Decision --}}
                <div class="bg-white border border-gray-100 rounded-2xl shadow-2xs p-5 sm:p-6">
                    <p class="text-sm font-bold text-gray-900 mb-1">Final Certification Decision</p>
                    <p class="text-xs text-gray-400 mb-4">Choose the outcome of this physical inspection.</p>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <label class="relative border-2 border-gray-200 has-checked:border-emerald-500 has-checked:bg-emerald-50/50 rounded-2xl p-4 flex items-center gap-3 cursor-pointer transition-all hover:border-emerald-300">
                            <input type="radio" name="decision" value="pass" checked class="peer sr-only" id="decision-pass">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Pass &amp; Certify</p>
                                <p class="text-xs text-gray-500">Meets standards — assign grade &amp; approve listing.</p>
                            </div>
                            <svg class="w-5 h-5 text-emerald-500 absolute top-3 right-3 opacity-0 peer-checked:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </label>

                        <label class="relative border-2 border-gray-200 has-checked:border-red-500 has-checked:bg-red-50/50 rounded-2xl p-4 flex items-center gap-3 cursor-pointer transition-all hover:border-red-300">
                            <input type="radio" name="decision" value="fail" class="peer sr-only" id="decision-fail">
                            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Fail &amp; Reject</p>
                                <p class="text-xs text-gray-500">Critical faults found — rejection reason required.</p>
                            </div>
                            <svg class="w-5 h-5 text-red-500 absolute top-3 right-3 opacity-0 peer-checked:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </label>
                    </div>
                </div>

                {{-- Pass Fields --}}
                <div data-tab-content="pass-fields" class="bg-white border border-gray-100 rounded-2xl shadow-2xs p-5 sm:p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Cosmetic Grade *</label>
                        <div class="grid grid-cols-3 gap-2.5">
                            <label class="relative border-2 border-gray-200 has-checked:border-emerald-500 has-checked:bg-emerald-50/60 rounded-xl p-3 text-center cursor-pointer transition-all hover:border-emerald-300">
                                <input type="radio" name="grade" value="A" checked class="sr-only">
                                <span class="block text-lg font-black text-emerald-600">A</span>
                                <span class="block text-[10px] font-semibold text-gray-500 mt-0.5">Like New</span>
                            </label>
                            <label class="relative border-2 border-gray-200 has-checked:border-blue-500 has-checked:bg-blue-50/60 rounded-xl p-3 text-center cursor-pointer transition-all hover:border-blue-300">
                                <input type="radio" name="grade" value="B" class="sr-only">
                                <span class="block text-lg font-black text-blue-600">B</span>
                                <span class="block text-[10px] font-semibold text-gray-500 mt-0.5">Good</span>
                            </label>
                            <label class="relative border-2 border-gray-200 has-checked:border-amber-500 has-checked:bg-amber-50/60 rounded-xl p-3 text-center cursor-pointer transition-all hover:border-amber-300">
                                <input type="radio" name="grade" value="C" class="sr-only">
                                <span class="block text-lg font-black text-amber-600">C</span>
                                <span class="block text-[10px] font-semibold text-gray-500 mt-0.5">Fair</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="battery_health_range" class="block text-xs font-semibold text-gray-700">Battery Health</label>
                            <span id="battery-health-display" class="text-xs font-bold text-brand-600">— %</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="range" id="battery_health_range" min="0" max="100" value="0" class="flex-1 accent-brand-500 h-2 cursor-pointer">
                            <input type="number" name="battery_health" id="battery_health_number" min="0" max="100" placeholder="94" class="w-20 py-2 px-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-800 text-center focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                </div>

                {{-- Fail Fields --}}
                <div data-tab-content="fail-fields" class="hidden bg-white border border-red-100 rounded-2xl shadow-2xs p-5 sm:p-6 space-y-2">
                    <label class="block text-xs font-semibold text-red-900">Rejection Reason *</label>
                    <textarea name="reason" rows="3" placeholder="Detail the physical or diagnostic failures encountered during inspection..." class="w-full py-2 px-3 bg-red-50/40 border border-red-200 rounded-xl text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                {{-- Additional Notes --}}
                <div class="bg-white border border-gray-100 rounded-2xl shadow-2xs p-5 sm:p-6">
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Inspector Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Internal or certificate notes regarding accessories, box condition, or serial numbers..." class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"></textarea>
                </div>

                {{-- Sticky Action Bar --}}
                <div class="sticky bottom-0 z-10 -mx-1 px-1 pt-1">
                    <div class="flex items-center justify-between gap-3 p-4 bg-white/95 backdrop-blur-xs border border-gray-100 rounded-2xl shadow-lg">
                        <a href="{{ route('verifier.appointments.index') }}" class="text-xs text-gray-500 hover:text-gray-800 font-semibold px-2">Cancel &amp; Return</a>
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-xl shadow-xs transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            <span>Submit Inspection Report</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('input[name="decision"]').forEach((input) => {
            input.addEventListener('change', function () {
                const isPass = this.value === 'pass';
                document.querySelector('[data-tab-content="pass-fields"]').classList.toggle('hidden', !isPass);
                document.querySelector('[data-tab-content="fail-fields"]').classList.toggle('hidden', isPass);
            });
        });

        const batteryRange = document.getElementById('battery_health_range');
        const batteryNumber = document.getElementById('battery_health_number');
        const batteryDisplay = document.getElementById('battery-health-display');

        function syncBattery(value) {
            const display = value === '' ? '— %' : `${value}%`;
            if (batteryDisplay) batteryDisplay.textContent = display;
        }

        batteryRange?.addEventListener('input', () => {
            batteryNumber.value = batteryRange.value;
            syncBattery(batteryRange.value);
        });

        batteryNumber?.addEventListener('input', () => {
            const clamped = Math.min(100, Math.max(0, Number(batteryNumber.value) || 0));
            batteryRange.value = batteryNumber.value === '' ? 0 : clamped;
            syncBattery(batteryNumber.value);
        });

        const failLabel = document.getElementById('checklist-progress-label');
        const totalChecklist = document.querySelectorAll('.checklist-row').length;

        function updateFailCount() {
            if (!failLabel) return;
            const failed = document.querySelectorAll('.checklist-input[value="fail"]:checked').length;
            failLabel.textContent = `${failed} / ${totalChecklist} Fail-Flagged`;
            failLabel.classList.toggle('text-red-600', failed > 0);
            failLabel.classList.toggle('text-gray-700', failed === 0);
        }

        document.querySelectorAll('.checklist-input').forEach((input) => {
            input.addEventListener('change', updateFailCount);
        });

        updateFailCount();
    </script>
@endsection
