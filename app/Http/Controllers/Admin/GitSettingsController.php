<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\Admin\GitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GitSettingsController extends Controller
{
    public function __construct(
        private readonly GitService $git
    ) {}

    /**
     * Display the Git repository settings and control center.
     */
    public function index(): View
    {
        $summary = $this->git->getSummary();

        return view('admin.settings.git', [
            'summary' => $summary,
            'consoleOutput' => session('console_output'),
        ]);
    }

    /**
     * Pull updates from the remote repository.
     */
    public function pull(Request $request): RedirectResponse
    {
        $remote = (string) $request->input('remote', 'origin');
        $branch = $request->filled('branch') ? (string) $request->input('branch') : null;
        $runMigrations = $request->boolean('run_migrations');
        $clearCache = $request->boolean('clear_cache', true);

        $result = $this->git->pull($remote, $branch, $runMigrations, $clearCache);

        ActivityLog::record('settings.git.pull', properties: [
            'remote' => $remote,
            'branch' => $branch,
            'success' => $result['success'],
            'exit_code' => $result['exit_code'],
            'duration_ms' => $result['duration_ms'],
        ]);

        Log::info('Admin git pull executed', $result);

        if (! $result['success']) {
            return back()
                ->with('error', 'Git pull failed: '.($result['error'] ?: 'Check console output for details.'))
                ->with('console_output', $result);
        }

        return back()
            ->with('status', 'Git repository pulled successfully.')
            ->with('console_output', $result);
    }

    /**
     * Fetch all remote branches and tags without merging.
     */
    public function fetch(Request $request): RedirectResponse
    {
        $remote = (string) $request->input('remote', 'origin');
        $result = $this->git->fetch($remote);

        ActivityLog::record('settings.git.fetch', properties: [
            'remote' => $remote,
            'success' => $result['success'],
            'exit_code' => $result['exit_code'],
        ]);

        if (! $result['success']) {
            return back()
                ->with('error', 'Git fetch failed: '.($result['error'] ?: 'Unknown error.'))
                ->with('console_output', $result);
        }

        return back()
            ->with('status', "Fetched references from remote [{$remote}] successfully.")
            ->with('console_output', $result);
    }

    /**
     * Switch / checkout to another existing branch.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'branch' => ['required', 'string', 'max:120'],
        ]);

        $branch = (string) $request->input('branch');
        $result = $this->git->checkout($branch);

        ActivityLog::record('settings.git.checkout', properties: [
            'branch' => $branch,
            'success' => $result['success'],
            'exit_code' => $result['exit_code'],
        ]);

        if (! $result['success']) {
            return back()
                ->with('error', 'Git checkout failed: '.($result['error'] ?: 'Make sure working tree is clean.'))
                ->with('console_output', $result);
        }

        return back()
            ->with('status', "Switched to branch [{$branch}].")
            ->with('console_output', $result);
    }

    /**
     * Stash uncommitted changes in the working directory.
     */
    public function stash(Request $request): RedirectResponse
    {
        $message = (string) $request->input('stash_message', 'Admin panel stash '.now()->toDateTimeString());
        $result = $this->git->stash($message);

        ActivityLog::record('settings.git.stash', properties: [
            'message' => $message,
            'success' => $result['success'],
        ]);

        if (! $result['success']) {
            return back()
                ->with('error', 'Git stash failed: '.($result['error'] ?: 'Unknown error.'))
                ->with('console_output', $result);
        }

        return back()
            ->with('status', 'Working tree changes successfully saved to stash.')
            ->with('console_output', $result);
    }

    /**
     * Pop the latest stash back to the working directory.
     */
    public function stashPop(): RedirectResponse
    {
        $result = $this->git->stashPop();

        ActivityLog::record('settings.git.stash_pop', properties: [
            'success' => $result['success'],
        ]);

        if (! $result['success']) {
            return back()
                ->with('error', 'Git stash pop failed: '.($result['error'] ?: 'Conflict or no stash found.'))
                ->with('console_output', $result);
        }

        return back()
            ->with('status', 'Stash applied and dropped successfully.')
            ->with('console_output', $result);
    }

    /**
     * Discard all local working directory changes in tracked files.
     */
    public function discard(): RedirectResponse
    {
        $result = $this->git->discardChanges();

        ActivityLog::record('settings.git.discard', properties: [
            'success' => $result['success'],
        ]);

        if (! $result['success']) {
            return back()
                ->with('error', 'Failed to discard changes: '.($result['error'] ?: 'Unknown error.'))
                ->with('console_output', $result);
        }

        return back()
            ->with('status', 'Local working tree changes discarded successfully.')
            ->with('console_output', $result);
    }

    /**
     * Stage all changes, commit, and optionally push to remote.
     */
    public function commit(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:255'],
            'push' => ['nullable', 'boolean'],
        ]);

        $message = (string) $request->input('message');
        $push = $request->boolean('push');

        $result = $this->git->commitAndPush($message, $push);

        ActivityLog::record('settings.git.commit', properties: [
            'message' => $message,
            'pushed' => $push,
            'success' => $result['success'],
        ]);

        if (! $result['success']) {
            return back()
                ->with('error', 'Git commit/push failed: '.($result['error'] ?: 'Check console output.'))
                ->with('console_output', $result);
        }

        $msg = $push ? 'Changes committed and pushed to remote origin.' : 'Changes committed locally.';

        return back()
            ->with('status', $msg)
            ->with('console_output', $result);
    }

    /**
     * Run a safe diagnostic inspection command.
     */
    public function runCommand(Request $request): RedirectResponse
    {
        $request->validate([
            'command' => ['required', 'string', 'in:status,diff,log-graph,remote-v,branch-a,config-list'],
        ]);

        $commandKey = (string) $request->input('command');
        $result = $this->git->runDiagnostic($commandKey);

        return back()
            ->with('status', "Executed `{$result['command']}`.")
            ->with('console_output', $result);
    }
}
