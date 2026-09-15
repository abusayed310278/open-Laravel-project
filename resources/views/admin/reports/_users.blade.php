    <div class="grid sm:grid-cols-2 gap-6 mt-6">
        <x-card title="New Registrations">
            <p class="text-3xl font-bold text-gray-900 mb-4">{{ number_format($d['total']) }}</p>
            <div class="space-y-2">
                @foreach ($d['byRole'] as $row)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $row['role'] }}</span>
                        <span class="text-gray-900 font-medium">{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="KYC Submissions">
            @if (empty($d['byKycStatus']))
                <p class="text-sm text-gray-400 text-center py-10">No KYC submissions in this range.</p>
            @else
                <div class="space-y-2">
                    @foreach ($d['byKycStatus'] as $row)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">{{ $row['status'] }}</span>
                            <span class="text-gray-900 font-medium">{{ $row['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
