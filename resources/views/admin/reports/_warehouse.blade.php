    <div class="grid sm:grid-cols-3 gap-4 mt-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Currently Stored</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['stored']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Released (range)</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['released']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Movements (range)</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['movements']) }}</p>
        </div>
    </div>
