@extends('layouts.admin')

@section('title', 'Settings — Git')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @if (isset($errors) && $errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    @if (! $summary['is_installed'])
        <x-alert type="error" class="mb-5">
            Git is not installed or not accessible in the server PATH.
        </x-alert>
    @elseif (! $summary['is_repository'])
        <x-alert type="warning" class="mb-5">
            This application directory is not a Git repository.
        </x-alert>
    @else
        <div class="space-y-6 max-w-4xl">
            {{-- 1. Simple Repository Status Card --}}
            <x-card title="Repository Overview">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="text-gray-400 block mb-1">Current Branch</span>
                        <span class="font-mono font-semibold text-gray-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $summary['branch'] }}
                        </span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="text-gray-400 block mb-1">Sync Status</span>
                        @if ($summary['sync']['behind'] > 0)
                            <span class="font-semibold text-amber-600">
                                {{ $summary['sync']['behind'] }} new commit(s) available
                            </span>
                        @elseif ($summary['sync']['ahead'] > 0)
                            <span class="font-semibold text-blue-600">
                                {{ $summary['sync']['ahead'] }} commit(s) ahead
                            </span>
                        @else
                            <span class="font-semibold text-emerald-600">Up to date</span>
                        @endif
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="text-gray-400 block mb-1">Working Tree</span>
                        @if ($summary['working_tree']['is_clean'])
                            <span class="font-semibold text-emerald-600">Clean (No changes)</span>
                        @else
                            <span class="font-semibold text-amber-600">
                                {{ $summary['working_tree']['total_changes'] }} modified file(s)
                            </span>
                        @endif
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="text-gray-400 block mb-1">Remote</span>
                        @if ($summary['web_url'])
                            <a href="{{ $summary['web_url'] }}" target="_blank" rel="noopener noreferrer" class="font-mono text-brand-600 hover:underline truncate block" title="{{ $summary['remote_url'] }}">
                                GitHub Repo &nearr;
                            </a>
                        @else
                            <span class="font-mono text-gray-700 truncate block">{{ $summary['remote_url'] ?? 'None' }}</span>
                        @endif
                    </div>
                </div>

                @if ($summary['latest_commit'])
                    <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-gray-500">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-semibold text-gray-800 bg-gray-100 px-2 py-0.5 rounded">
                                {{ $summary['latest_commit']['short_hash'] }}
                            </span>
                            <span class="text-gray-700 font-medium truncate max-w-md">
                                {{ $summary['latest_commit']['message'] }}
                            </span>
                        </div>
                        <span class="text-gray-400 shrink-0">
                            {{ $summary['latest_commit']['author_name'] }} &bull; {{ $summary['latest_commit']['relative_date'] }}
                        </span>
                    </div>
                @endif
            </x-card>

            {{-- 2. Simple Action Operations --}}
            <x-card title="Git Actions">
                <div class="space-y-4">
                    {{-- Action 1: Pull Updates --}}
                    <div class="border border-gray-100 rounded-lg p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-gray-800">Pull Latest Changes</h3>
                            <p class="text-xs text-gray-500">
                                Pulls the newest code updates from GitHub/remote into the <code>{{ $summary['branch'] }}</code> branch.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.git.pull') }}" onsubmit="return confirm('Pull latest code updates from remote repository?');" class="flex items-center gap-3 shrink-0">
                            @csrf
                            <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
                                <input type="checkbox" name="clear_cache" value="1" checked class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>Clear Cache</span>
                            </label>

                            <button
                                type="submit"
                                class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-md shadow-xs transition-colors flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Pull Updates
                            </button>
                        </form>
                    </div>

                    {{-- Action 2: Check for Updates (Fetch) --}}
                    <div class="border border-gray-100 rounded-lg p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-gray-800">Check for Updates (Fetch)</h3>
                            <p class="text-xs text-gray-500">
                                Checks remote repository for new commits without modifying any local files.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.git.fetch') }}" class="shrink-0">
                            @csrf
                            <button
                                type="submit"
                                class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-md border border-gray-200 transition-colors flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Check Updates
                            </button>
                        </form>
                    </div>

                    {{-- Action 3: Discard Local Changes (only if not clean) --}}
                    @if (! $summary['working_tree']['is_clean'])
                        <div class="border border-amber-200 bg-amber-50/40 rounded-lg p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h3 class="text-sm font-semibold text-amber-900">Discard Local Modifications</h3>
                                <p class="text-xs text-amber-700">
                                    You have {{ $summary['working_tree']['total_changes'] }} modified file(s). Discard local edits to allow clean pulling.
                                </p>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.git.discard') }}" onsubmit="return confirm('Discard all local modifications in tracked files?');" class="shrink-0">
                                @csrf
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-white hover:bg-red-50 text-red-700 text-xs font-semibold rounded-md border border-red-200 transition-colors"
                                >
                                    Discard Changes
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- 3. Command Output (Only displayed after running an action) --}}
            @if (! empty($consoleOutput))
                <x-card title="Last Command Result">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                        <span class="font-mono text-gray-700">$ {{ $consoleOutput['command'] ?? 'git' }}</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ ($consoleOutput['success'] ?? false) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ($consoleOutput['success'] ?? false) ? 'Success' : 'Failed' }} ({{ $consoleOutput['duration_ms'] ?? 0 }}ms)
                        </span>
                    </div>
                    <pre class="bg-gray-900 text-gray-100 p-3 rounded-md text-xs font-mono overflow-x-auto whitespace-pre-wrap">{{ $consoleOutput['output'] ?: ($consoleOutput['error'] ?? 'Completed.') }}</pre>
                </x-card>
            @endif
        </div>
    @endif
@endsection
