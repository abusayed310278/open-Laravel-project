@extends('layouts.app')

@section('title', 'Compare Products')

@section('content')
    @php
        $ids = $products->pluck('id');
        $rows = [
            'Price' => fn ($p) => 'Tk '.number_format($p->price),
            'Condition' => fn ($p) => ucfirst($p->condition->value),
            'Brand' => fn ($p) => $p->brand?->name ?? '—',
            'Category' => fn ($p) => $p->category?->name ?? '—',
            'Seller' => fn ($p) => $p->user->isAdmin()
                ? config('app.name').' Official'
                : ($p->user->businessProfile?->business_name ?? $p->user->salerProfile?->display_name ?? $p->user->name),
            'Verified' => fn ($p) => $p->isVerified() ? 'Yes' : 'No',
        ];
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10" id="compare-page" data-has-ids="{{ $products->isNotEmpty() ? '1' : '0' }}">
        <x-breadcrumb :items="['Compare' => null]" class="mb-6" />

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-extrabold text-gray-950">Product Comparison</h1>

            @if ($products->isNotEmpty())
                <div class="flex items-center gap-2">
                    <button type="button" id="compare-share-btn" data-url="{{ str_replace('%2C', ',', route('compare', ['ids' => $ids->implode(',')])) }}"
                            class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <span>Share link</span>
                    </button>
                    <button type="button" id="compare-clear-btn" class="rounded-md px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-900 cursor-pointer">Clear</button>
                </div>
            @endif
        </div>

        @if ($products->isEmpty())
            <div id="compare-empty" class="rounded-xl border border-dashed border-gray-200 py-16 text-center">
                <p class="text-base font-semibold text-gray-700">No products to compare yet.</p>
                <p class="mt-1 text-sm text-gray-400">Click the compare icon on up to {{ $maxProducts }} product cards, then come back here.</p>
                <a href="{{ route('shop') }}" class="mt-5 inline-block rounded-md bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600">Browse products</a>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full min-w-[640px] table-fixed text-sm">
                    <thead>
                        <tr class="align-top">
                            <th class="w-28 bg-gray-50 p-4"></th>
                            @foreach ($products as $product)
                                <th class="p-4 text-left font-normal border-l border-gray-100">
                                    <a href="{{ route('products.show', $product) }}" class="block">
                                        <div class="mb-3 flex h-36 w-full items-center justify-center overflow-hidden rounded-lg bg-gray-50 p-2">
                                            <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->title }}" class="h-full w-full object-contain">
                                        </div>
                                        <span class="line-clamp-2 font-bold text-gray-900 hover:text-brand-600">{{ $product->title }}</span>
                                    </a>
                                    <a href="{{ $products->count() > 1 ? route('compare', ['ids' => $ids->reject(fn ($id) => $id === $product->id)->implode(',')]) : route('compare') }}"
                                       data-compare-remove="{{ $product->id }}"
                                       class="mt-2 inline-block text-xs font-semibold text-red-600 hover:underline">Remove</a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($rows as $label => $value)
                            <tr>
                                <th class="bg-gray-50 p-4 text-left font-semibold text-gray-600">{{ $label }}</th>
                                @foreach ($products as $product)
                                    <td class="p-4 border-l border-gray-100 {{ $label === 'Price' ? 'font-extrabold text-brand-600' : 'text-gray-800' }}">{{ $value($product) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr>
                            <th class="bg-gray-50 p-4"></th>
                            @foreach ($products as $product)
                                <td class="p-4 border-l border-gray-100">
                                    <button type="button" data-product-id="{{ $product->id }}" class="js-add-to-cart w-full rounded-lg bg-brand-500 px-3 py-2 text-xs font-bold text-white hover:bg-brand-600 cursor-pointer">Add to Cart</button>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div id="compare-share-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <h2 class="text-lg font-extrabold text-gray-950">Share this comparison</h2>
            <p class="mt-1 text-sm text-gray-500">Anyone with this link sees the same products.</p>
            <input type="text" id="compare-share-input" readonly class="mt-4 w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400">
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="compare-share-close" class="rounded-md border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer">Close</button>
                <button type="button" id="compare-share-copy" class="rounded-md bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600 cursor-pointer">Copy link</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const KEY = 'openbox_compare_items';
            const read = () => { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } };
            const write = (items) => { try { localStorage.setItem(KEY, JSON.stringify(items)); } catch (e) {} };
            const page = document.getElementById('compare-page');

            // Opened without a shared list: fall back to this browser's saved list.
            if (page.dataset.hasIds === '0' && ! new URLSearchParams(window.location.search).has('ids')) {
                const ids = read().map(item => item.id).join(',');
                if (ids) window.location.replace(@json(route('compare')) + '?ids=' + encodeURIComponent(ids));
                return;
            }

            document.querySelectorAll('[data-compare-remove]').forEach(link => {
                link.addEventListener('click', () => {
                    write(read().filter(item => String(item.id) !== link.dataset.compareRemove));
                });
            });

            document.getElementById('compare-clear-btn')?.addEventListener('click', () => {
                write([]);
                window.location.href = @json(route('compare'));
            });

            const shareBtn = document.getElementById('compare-share-btn');
            const modal = document.getElementById('compare-share-modal');
            const input = document.getElementById('compare-share-input');
            const copyBtn = document.getElementById('compare-share-copy');
            const closeModal = () => modal.classList.add('hidden');

            shareBtn?.addEventListener('click', () => {
                input.value = shareBtn.dataset.url;
                copyBtn.textContent = 'Copy link';
                modal.classList.remove('hidden');
                input.select();
            });

            copyBtn?.addEventListener('click', async () => {
                input.select();
                try {
                    await navigator.clipboard.writeText(input.value);
                } catch (e) {
                    document.execCommand('copy');
                }
                copyBtn.textContent = 'Copied!';
            });

            document.getElementById('compare-share-close')?.addEventListener('click', closeModal);
            modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
        });
    </script>
@endsection
