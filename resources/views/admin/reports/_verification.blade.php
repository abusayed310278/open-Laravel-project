    <div class="grid sm:grid-cols-4 gap-4 mt-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Appointments</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['appointments']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Passed</p>
            <p class="text-xl font-bold text-green-600">{{ number_format($d['passed']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Failed</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($d['failed']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Pass Rate</p>
            <p class="text-xl font-bold text-gray-900">{{ $d['passRate'] }}%</p>
        </div>
    </div>
