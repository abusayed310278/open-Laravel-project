    <div class="grid sm:grid-cols-4 gap-4 mt-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Active</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['active']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Expired</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['expired']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Cancelled</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['cancelled']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Revenue (range)</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($d['revenue'], 2) }}</p>
        </div>
    </div>
