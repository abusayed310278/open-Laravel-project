    <div class="grid sm:grid-cols-4 gap-4 mt-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total Products</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['total']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Published</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['published']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Verified</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['verified']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Warehoused</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['warehoused']) }}</p>
        </div>
    </div>

    <x-card title="By Status" class="mt-6">
        <div class="space-y-2">
            @foreach ($d['byStatus'] as $row)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ $row['status'] }}</span>
                    <span class="text-gray-900 font-medium">{{ $row['count'] }}</span>
                </div>
            @endforeach
        </div>
    </x-card>
