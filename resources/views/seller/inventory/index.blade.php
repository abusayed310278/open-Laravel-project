@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Inventory')

@section('content')
    <div class="grid lg:grid-cols-2 gap-6">
        <x-card title="Stock Levels">
            <x-table :headers="['Product', 'Stock', '']" id="inventory-stock-table">
                @forelse ($products as $product)
                    <tr class="border-b border-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $product->title }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$product->quantity <= 0 ? 'red' : ($product->quantity <= 5 ? 'amber' : 'green')">
                                {{ $product->quantity }} in stock
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route($routePrefix.'products.edit', $product) }}" class="text-brand-600 font-medium hover:underline text-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400 text-sm">No products yet.</td>
                    </tr>
                @endforelse
            </x-table>
            <x-pagination :paginator="$products" />
        </x-card>

        <x-card title="Recent Movements">
            <x-table :headers="['Product', 'Type', 'Change', 'Date']" id="inventory-movements-table">
                @forelse ($movements as $movement)
                    <tr class="border-b border-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $movement->product->title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $movement->type->label() }}</td>
                        <td class="px-4 py-3 {{ $movement->quantity >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $movement->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No movements yet.</td>
                    </tr>
                @endforelse
            </x-table>
            <x-pagination :paginator="$movements" />
        </x-card>
    </div>
@endsection
