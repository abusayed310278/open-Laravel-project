@extends('layouts.verifier')

@section('title', 'Product Inspection & Certification')

@section('content')
    <div class="mb-5">
        <x-breadcrumb :items="['Inspection Queue' => route('verifier.appointments.index'), $verification->product->title => null]" />
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left: Product Profile & Seller Info --}}
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Device Details">
                <div class="rounded-xl overflow-hidden border border-gray-100 mb-4 bg-gray-50">
                    <img src="{{ $verification->product->primaryImageUrl() }}" alt="{{ $verification->product->title }}" class="w-full h-48 object-cover">
                </div>

                <h2 class="font-bold text-base text-gray-950 mb-1">{{ $verification->product->title }}</h2>
                <div class="flex items-center gap-2 mb-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                        {{ $verification->product->category?->name ?? 'Electronics' }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-800">
                        {{ $verification->product->condition?->label() ?? 'Pre-owned' }}
                    </span>
                </div>

                <dl class="space-y-2.5 text-xs text-gray-600 divide-y divide-gray-50">
                    <div class="flex justify-between items-center pt-2">
                        <dt class="text-gray-400">Seller</dt>
                        <dd class="font-medium text-gray-900 flex items-center gap-1.5">
                            <span>{{ $verification->seller->name }}</span>
                            <form method="POST" action="{{ route('chat.start', $verification->product) }}" class="inline-block">
                                @csrf
                                <button type="submit" class="p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition cursor-pointer" title="Chat with {{ $verification->seller->name }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                            </form>
                        </dd>
                    </div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">Seller Email</dt><dd class="font-medium text-gray-900">{{ $verification->seller->email }}</dd></div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">SKU / Code</dt><dd class="font-mono text-gray-800">{{ $verification->product->sku ?? '—' }}</dd></div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">Scheduled Date</dt><dd class="font-medium text-gray-800">{{ $verification->scheduled_at?->format('M j, Y g:i A') ?? 'Pending' }}</dd></div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">Inspection Hub</dt><dd class="font-medium text-gray-800">{{ $verification->location?->name ?? 'Default Hub' }}</dd></div>
                </dl>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <form method="POST" action="{{ route('chat.start', $verification->product) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-200 border border-gray-200 text-gray-800 hover:text-amber-800 rounded-xl text-xs font-semibold transition cursor-pointer">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Message Seller Regarding Inspection</span>
                        </button>
                    </form>
                </div>

                @if ($verification->product->attributeValues->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">Specifications</p>
                        <dl class="space-y-1.5 text-xs">
                            @foreach ($verification->product->attributeValues as $val)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">{{ $val->attribute?->name ?? 'Spec' }}</dt>
                                    <dd class="font-medium text-gray-800">{{ $val->displayValue() }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </x-card>

            {{-- Grade Guidelines Helper Card --}}
            <div class="bg-amber-500/5 border border-amber-200/60 rounded-2xl p-4 text-xs space-y-2">
                <p class="font-bold text-amber-950 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Openbox Grading Standard
                </p>
                <ul class="space-y-1.5 text-gray-600">
                    <li><strong class="text-gray-900">Grade A (Like New):</strong> Flawless cosmetic state, no scratches, battery health ≥ 85%.</li>
                    <li><strong class="text-gray-900">Grade B (Good):</strong> Minor micro-scratches on casing, screen intact, battery health ≥ 80%.</li>
                    <li><strong class="text-gray-900">Grade C (Fair):</strong> Visible signs of usage/scuffs, 100% functional motherboard & parts.</li>
                </ul>
            </div>
        </div>

        {{-- Right: Inspection Form & Checklist --}}
        <div class="lg:col-span-2">
            <x-card title="Physical Inspection Checklist & Decision">
                <form method="POST" action="{{ route('verifier.appointments.submit', $verification) }}" class="space-y-6">
                    @csrf

                    @if ($checklist->isEmpty())
                        <div class="p-4 bg-gray-50 rounded-xl text-xs text-gray-500">
                            General electronics inspection: Test power, screen, sensors, ports, and battery before grading.
                        </div>
                    @else
                        <div class="space-y-3">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Multi-Point Diagnostic Check</p>
                            @foreach ($checklist as $item)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border border-gray-100 rounded-xl p-3.5 hover:border-gray-200 transition-colors bg-white">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $item->item_name }}</p>
                                        @if ($item->description)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                    <div class="inline-flex items-center gap-2 p-1 bg-gray-50 rounded-lg border border-gray-100 self-start sm:self-auto">
                                        <label class="flex items-center gap-1 px-2.5 py-1 rounded cursor-pointer text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors">
                                            <input type="radio" name="results[{{ $item->id }}]" value="pass" class="accent-emerald-600" checked>
                                            <span>Pass</span>
                                        </label>
                                        <label class="flex items-center gap-1 px-2.5 py-1 rounded cursor-pointer text-xs font-semibold text-red-700 hover:bg-red-50 transition-colors">
                                            <input type="radio" name="results[{{ $item->id }}]" value="fail" class="accent-red-600">
                                            <span>Fail</span>
                                        </label>
                                        <label class="flex items-center gap-1 px-2.5 py-1 rounded cursor-pointer text-xs font-semibold text-gray-600 hover:bg-gray-200 transition-colors">
                                            <input type="radio" name="results[{{ $item->id }}]" value="na" class="accent-gray-600">
                                            <span>N/A</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <hr class="border-gray-100">

                    {{-- Decision Selector --}}
                    <div>
                        <p class="text-sm font-bold text-gray-900 mb-3">Final Certification Decision</p>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="border border-gray-200 has-checked:border-emerald-500 has-checked:bg-emerald-50/40 rounded-xl p-3.5 flex items-center gap-3 cursor-pointer transition-all">
                                <input type="radio" name="decision" value="pass" checked class="accent-emerald-600 w-4 h-4">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Pass & Certify</p>
                                    <p class="text-xs text-gray-500">Device meets standards. Assign grade and approve listing.</p>
                                </div>
                            </label>

                            <label class="border border-gray-200 has-checked:border-red-500 has-checked:bg-red-50/40 rounded-xl p-3.5 flex items-center gap-3 cursor-pointer transition-all">
                                <input type="radio" name="decision" value="fail" class="accent-red-600 w-4 h-4">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Fail & Reject</p>
                                    <p class="text-xs text-gray-500">Device has critical faults. Rejection reason required.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Pass Fields --}}
                    <div data-tab-content="pass-fields" class="space-y-4 p-4 bg-emerald-50/30 rounded-xl border border-emerald-100">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Cosmetic Grade *</label>
                                <select name="grade" class="w-full py-2 px-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="A">Grade A · Like New (Mint condition)</option>
                                    <option value="B">Grade B · Good (Minor signs of use)</option>
                                    <option value="C">Grade C · Fair (Visible wear, fully functional)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Battery Health (%)</label>
                                <input type="number" name="battery_health" min="0" max="100" placeholder="e.g. 94" class="w-full py-2 px-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>

                    {{-- Fail Fields --}}
                    <div data-tab-content="fail-fields" class="hidden space-y-2 p-4 bg-red-50/30 rounded-xl border border-red-100">
                        <label class="block text-xs font-semibold text-red-900">Rejection Reason *</label>
                        <textarea name="reason" rows="3" placeholder="Detail the physical or diagnostic failures encountered during inspection..." class="w-full py-2 px-3 bg-white border border-red-200 rounded-xl text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    {{-- Additional Notes --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Inspector Notes (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="Internal or certificate notes regarding accessories, box condition, or serial numbers..." class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('verifier.appointments.index') }}" class="text-xs text-gray-500 hover:text-gray-800 font-medium">Cancel & Return</a>
                        <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-xs transition-colors cursor-pointer">
                            Submit Inspection Report
                        </button>
                    </div>
                </form>
            </x-card>
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
    </script>
@endsection
