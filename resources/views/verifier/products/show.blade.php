@extends('layouts.verifier')

@section('title', 'Product Inspection: ' . $product->title)

@section('content')
    <div class="mb-5">
        <x-breadcrumb :items="['Seller Products' => route('verifier.products.index'), $product->title => null]" />
    </div>

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Product Summary & Specifications --}}
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Product Gallery">
                <div class="rounded-xl overflow-hidden border border-gray-100 mb-3 bg-gray-50 relative min-h-[14rem] flex items-center justify-center">
                    @if ($product->primaryImageUrl())
                        <img id="verifier-main-image" src="{{ $product->primaryImageUrl() }}" alt="{{ $product->title }}" class="w-full h-56 object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                        <div class="hidden w-full h-56 flex items-center justify-center text-gray-400 bg-gray-100">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                            </svg>
                        </div>
                    @else
                        <div class="w-full h-56 flex items-center justify-center text-gray-400 bg-gray-100">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                            </svg>
                        </div>
                    @endif
                </div>
                @if ($product->images->count() > 1)
                    <div class="flex gap-2 overflow-x-auto pb-1">
                        @foreach ($product->images as $img)
                            <button type="button" onclick="const main = document.getElementById('verifier-main-image'); if(main){ main.src='{{ $img->url() }}'; main.classList.remove('hidden'); if(main.nextElementSibling) main.nextElementSibling.classList.add('hidden'); }" class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 hover:border-amber-400 flex-shrink-0 transition bg-gray-50 flex items-center justify-center">
                                <img src="{{ $img->url() }}" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                <div class="hidden w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                    </svg>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <x-card title="Seller & Listing Info">
                <dl class="space-y-2.5 text-xs text-gray-600 divide-y divide-gray-50">
                    <div class="flex justify-between items-center pt-2">
                        <dt class="text-gray-400">Seller</dt>
                        <dd class="font-bold text-gray-900 flex items-center gap-1.5">
                            <span>{{ $product->user->name ?? 'Unknown' }}</span>
                            @if ($product->user)
                                <form method="POST" action="{{ route('chat.start', $product) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition cursor-pointer" title="Chat with {{ $product->user->name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </button>
                                </form>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">Email</dt><dd class="font-medium text-gray-800">{{ $product->user->email ?? '—' }}</dd></div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">Role</dt><dd class="font-medium text-gray-800">{{ $product->user?->role?->label() ?? 'Seller' }}</dd></div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">Price</dt><dd class="font-bold text-gray-900">${{ number_format($product->price, 2) }}</dd></div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">Condition</dt><dd class="font-semibold text-gray-800">{{ $product->condition->label() }}</dd></div>
                    <div class="flex justify-between pt-2"><dt class="text-gray-400">SKU</dt><dd class="font-mono text-gray-800">{{ $product->sku ?? '—' }}</dd></div>
                    <div class="flex justify-between pt-2">
                        <dt class="text-gray-400">Verification Status</dt>
                        <dd>
                            <x-badge :color="$product->verification_status->badgeColor()">{{ $product->verification_status->label() }}</x-badge>
                        </dd>
                    </div>
                    @if ($product->grade)
                        <div class="flex justify-between pt-2"><dt class="text-gray-400">Assigned Grade</dt><dd class="font-bold text-emerald-700">Grade {{ $product->grade instanceof \App\Enums\ProductGrade ? $product->grade->value : $product->grade }}</dd></div>
                    @endif
                </dl>

                @if ($product->user)
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <form method="POST" action="{{ route('chat.start', $product) }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-200 border border-gray-200 text-gray-800 hover:text-amber-800 rounded-xl text-xs font-semibold transition cursor-pointer">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>Message Seller</span>
                            </button>
                        </form>
                    </div>
                @endif

                @if ($product->attributeValues->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">Technical Specs</p>
                        <dl class="space-y-1.5 text-xs">
                            @foreach ($product->attributeValues as $val)
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">{{ $val->attribute?->name ?? 'Attribute' }}</dt>
                                    <dd class="font-medium text-gray-800">{{ $val->displayValue() }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Verification Panel / Checklist --}}
        <div class="lg:col-span-2">
            <x-card title="Verification & Diagnostic Center">
                @if ($product->isVerified())
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl mb-6 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-emerald-950">Certified Openbox Device (Grade {{ $product->grade }})</h3>
                            <p class="text-xs text-emerald-800 mt-0.5">This product has been physically certified. <strong>The seller is locked from editing its core attributes, photos, and price.</strong></p>
                            @if ($product->grade_notes)
                                <p class="text-xs text-emerald-700 mt-1 italic">“{{ $product->grade_notes }}”</p>
                            @endif
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('verifier.products.verify', $product) }}" class="space-y-6">
                    @csrf

                    @if ($checklist->isNotEmpty())
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Diagnostic Checklist (Category: {{ $product->category->name }})</p>
                            <div class="space-y-2.5">
                                @foreach ($checklist as $item)
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:border-gray-200 transition bg-white">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">{{ $item->item_name }}</p>
                                            @if ($item->description)
                                                <p class="text-[11px] text-gray-400">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                        <div class="inline-flex items-center gap-2 p-1 bg-gray-50 rounded-lg border border-gray-100 text-xs font-semibold">
                                            <label class="flex items-center gap-1 px-2 py-0.5 text-emerald-700 cursor-pointer">
                                                <input type="radio" name="results[{{ $item->id }}]" value="pass" class="accent-emerald-600" checked>
                                                <span>Pass</span>
                                            </label>
                                            <label class="flex items-center gap-1 px-2 py-0.5 text-red-700 cursor-pointer">
                                                <input type="radio" name="results[{{ $item->id }}]" value="fail" class="accent-red-600">
                                                <span>Fail</span>
                                            </label>
                                            <label class="flex items-center gap-1 px-2 py-0.5 text-gray-600 cursor-pointer">
                                                <input type="radio" name="results[{{ $item->id }}]" value="na" class="accent-gray-600">
                                                <span>N/A</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <hr class="border-gray-100">
                    @endif

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Certification Decision</p>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="border border-gray-200 has-checked:border-emerald-500 has-checked:bg-emerald-50/40 rounded-xl p-3 flex items-center gap-3 cursor-pointer transition-all">
                                <input type="radio" name="decision" value="pass" checked class="accent-emerald-600 w-4 h-4" onchange="toggleShowDecision(this)">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Pass & Certify (Grade Device)</p>
                                    <p class="text-[11px] text-gray-500">Locks product from seller modification</p>
                                </div>
                            </label>

                            <label class="border border-gray-200 has-checked:border-red-500 has-checked:bg-red-50/40 rounded-xl p-3 flex items-center gap-3 cursor-pointer transition-all">
                                <input type="radio" name="decision" value="fail" class="accent-red-600 w-4 h-4" onchange="toggleShowDecision(this)">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Fail & Reject</p>
                                    <p class="text-[11px] text-gray-500">Device has critical faults</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="show-pass-fields" class="space-y-3 p-4 bg-emerald-50/40 border border-emerald-100 rounded-xl">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Assigned Grade *</label>
                                <select name="grade" class="w-full py-2 px-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="A" {{ ($product->grade ?? 'A') === 'A' ? 'selected' : '' }}>Grade A · Like New (Mint condition)</option>
                                    <option value="B" {{ ($product->grade ?? '') === 'B' ? 'selected' : '' }}>Grade B · Good (Minor micro-scratches)</option>
                                    <option value="C" {{ ($product->grade ?? '') === 'C' ? 'selected' : '' }}>Grade C · Fair (Visible wear, fully working)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Battery Health (%)</label>
                                <input type="number" name="battery_health" min="0" max="100" placeholder="e.g. 92" class="w-full py-2 px-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>

                    <div id="show-fail-fields" class="hidden space-y-1.5 p-4 bg-red-50/40 border border-red-100 rounded-xl">
                        <label class="block text-xs font-semibold text-red-900">Rejection Reason *</label>
                        <textarea name="reason" rows="2" placeholder="Specify why the device failed verification..." class="w-full py-2 px-3 bg-white border border-red-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Verifier Notes / Certificate Details</label>
                        <textarea name="notes" rows="2" placeholder="Notes on packaging, accessories, or screen condition..." class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">{{ $product->grade_notes }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <a href="{{ route('verifier.products.index') }}" class="text-xs text-gray-500 hover:text-gray-800 font-medium">← Back to Product Inventory</a>
                        <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition cursor-pointer">
                            {{ $product->isVerified() ? 'Update & Re-Certify Grade' : 'Verify & Lock Product' }}
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        function toggleShowDecision(radio) {
            const isPass = radio.value === 'pass';
            document.getElementById('show-pass-fields')?.classList.toggle('hidden', !isPass);
            document.getElementById('show-fail-fields')?.classList.toggle('hidden', isPass);
        }
    </script>
@endsection
