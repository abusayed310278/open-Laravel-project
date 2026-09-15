@extends('layouts.verifier')

@section('title', 'Inspect Product')

@section('content')
    <x-breadcrumb :items="['Appointments' => route('verifier.appointments.index'), $verification->product->title => null]" />

    <div class="grid md:grid-cols-3 gap-5">
        <x-card class="md:col-span-1" title="Product">
            @if ($verification->product->images->isNotEmpty())
                <img src="{{ $verification->product->images->first()->url() }}" class="w-full h-40 object-cover rounded-md mb-4">
            @endif
            <p class="font-semibold text-gray-900 mb-1">{{ $verification->product->title }}</p>
            <p class="text-sm text-gray-500 mb-4">{{ $verification->product->category->name }} · {{ $verification->product->condition->label() }}</p>

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Seller</dt><dd class="text-gray-800">{{ $verification->seller->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">SKU</dt><dd class="text-gray-800">{{ $verification->product->sku ?? '—' }}</dd></div>
            </dl>

            @if ($verification->product->attributeValues->isNotEmpty())
                <hr class="border-gray-100 my-4">
                <dl class="space-y-2 text-sm">
                    @foreach ($verification->product->attributeValues as $value)
                        <div class="flex justify-between">
                            <dt class="text-gray-400">{{ $value->attribute->name }}</dt>
                            <dd class="text-gray-800">{{ $value->displayValue() }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </x-card>

        <x-card class="md:col-span-2" title="Inspection Checklist">
            <form method="POST" action="{{ route('verifier.appointments.submit', $verification) }}" class="space-y-6">
                @csrf

                @if ($checklist->isEmpty())
                    <p class="text-sm text-gray-400">No checklist items configured — record notes below and assign a decision.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($checklist as $item)
                            <div class="flex items-center justify-between border border-gray-100 rounded-md p-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $item->item_name }}</p>
                                    @if ($item->description)
                                        <p class="text-xs text-gray-400">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <label class="flex items-center gap-1"><input type="radio" name="results[{{ $item->id }}]" value="pass" class="accent-brand-500" checked> Pass</label>
                                    <label class="flex items-center gap-1"><input type="radio" name="results[{{ $item->id }}]" value="fail" class="accent-brand-500"> Fail</label>
                                    <label class="flex items-center gap-1"><input type="radio" name="results[{{ $item->id }}]" value="na" class="accent-brand-500"> N/A</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <hr class="border-gray-100">

                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-2">Decision</p>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="decision" value="pass" checked class="accent-brand-500"> Pass — assign grade</label>
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="decision" value="fail" class="accent-brand-500"> Fail — reject listing</label>
                    </div>
                </div>

                <div data-tab-content="pass-fields" class="grid sm:grid-cols-2 gap-4">
                    <x-select label="Grade" name="grade" :options="['A' => 'Grade A · Like New', 'B' => 'Grade B · Good', 'C' => 'Grade C · Fair']" />
                    <x-input label="Battery health (%)" name="battery_health" type="number" min="0" max="100" />
                </div>

                <div data-tab-content="fail-fields" class="hidden">
                    <x-textarea label="Rejection reason" name="reason" rows="3" />
                </div>

                <x-textarea label="Notes (optional)" name="notes" rows="3" />

                <x-button type="submit">Submit Inspection</x-button>
            </form>
        </x-card>
    </div>

    <script>
        document.querySelectorAll('input[name="decision"]').forEach((input) => {
            input.addEventListener('change', function () {
                document.querySelector('[data-tab-content="pass-fields"]').classList.toggle('hidden', this.value !== 'pass');
                document.querySelector('[data-tab-content="fail-fields"]').classList.toggle('hidden', this.value !== 'fail');
            });
        });
    </script>
@endsection
