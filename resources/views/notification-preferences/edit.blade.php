@extends($layout)

@section('title', 'Notification Preferences')

@section($section)
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Notification Preferences</h1>

        @session('status')
            <x-alert type="success">{{ $value }}</x-alert>
        @endsession

        <x-card>
            <p class="text-sm text-gray-500 mb-5">In-app notifications are always delivered. Choose which categories also send you an email.</p>

            <form method="POST" action="{{ route('notification-preferences.update') }}" class="space-y-4">
                @csrf
                @foreach ($categories as $category)
                    <label class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                        <span class="text-sm font-medium text-gray-700">{{ $category->label() }}</span>
                        <input type="checkbox" name="categories[{{ $category->value }}]" value="1" @checked($preferences->get($category->value, true)) class="w-4 h-4 rounded accent-brand-500">
                    </label>
                @endforeach

                <x-button type="submit">Save Preferences</x-button>
            </form>
        </x-card>
    </div>
@endsection
