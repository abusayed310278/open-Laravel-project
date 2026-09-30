<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class GitService
{
    /**
     * Whitelist of safe diagnostic commands for read-only inspection.
     *
     * @var array<string, array{cmd: array<int, string>, label: string}>
     */
    private const array SAFE_DIAGNOSTICS = [
        'status' => ['cmd' => ['git', 'status'], 'label' => 'git status'],
        'diff' => ['cmd' => ['git', 'diff'], 'label' => 'git diff'],
        'log-graph' => ['cmd' => ['git', 'log', '--graph', '--oneline', '--decorate', '-n', '15'], 'label' => 'git log --graph -n 15'],
        'remote-v' => ['cmd' => ['git', 'remote', '-v'], 'label' => 'git remote -v'],
        'branch-a' => ['cmd' => ['git', 'branch', '-a'], 'label' => 'git branch -a'],
        'config-list' => ['cmd' => ['git', 'config', '--list', '--local'], 'label' => 'git config --list --local'],
    ];

    /**
     * Determine if git CLI is installed and accessible in the system environment.
     */
    public function isGitInstalled(): bool
    {
        $result = $this->executeCommand(['git', '--version']);

        return $result['success'];
    }

    /**
     * Get the detected Git CLI version string.
     */
    public function getGitVersion(): ?string
    {
        $result = $this->executeCommand(['git', '--version']);

        return $result['success'] ? trim($result['output']) : null;
    }

    /**
     * Check if the application root directory is a Git repository.
     */
    public function isRepository(): bool
    {
        if (! File::isDirectory(base_path('.git')) && ! File::isFile(base_path('.git'))) {
            return false;
        }

        $result = $this->executeCommand(['git', 'rev-parse', '--is-inside-work-tree']);

        return $result['success'] && trim($result['output']) === 'true';
    }

    /**
     * Get the current active branch name.
     */
    public function getCurrentBranch(): string
    {
        $result = $this->executeCommand(['git', 'branch', '--show-current']);

        if ($result['success'] && ! empty(trim($result['output']))) {
            return trim($result['output']);
        }

        $rev = $this->executeCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);

        return $rev['success'] ? trim($rev['output']) : 'HEAD';
    }

    /**
     * Get the remote URL for origin.
     */
    public function getRemoteUrl(string $remote = 'origin'): ?string
    {
        $result = $this->executeCommand(['git', 'remote', 'get-url', $remote]);

        if ($result['success'] && ! empty(trim($result['output']))) {
            return trim($result['output']);
        }

        $fallback = $this->executeCommand(['git', 'config', '--get', "remote.{$remote}.url"]);

        return $fallback['success'] && ! empty(trim($fallback['output'])) ? trim($fallback['output']) : null;
    }

    /**
     * Convert remote Git URL to standard HTTPS web URL (GitHub, GitLab, Bitbucket).
     */
    public function getWebUrl(?string $remoteUrl = null): ?string
    {
        $url = $remoteUrl ?? $this->getRemoteUrl();

        if (empty($url)) {
            return null;
        }

        if (str_starts_with($url, 'git@')) {
            $url = preg_replace('/^git@([^:]+):(.+)$/', 'https://$1/$2', $url);
        }

        $url = preg_replace('/\.git$/', '', (string) $url);

        if (str_starts_with((string) $url, 'http://') || str_starts_with((string) $url, 'https://')) {
            return $url;
        }

        return null;
    }

    /**
     * Retrieve the latest commit details.
     *
     * @return array{hash: string, short_hash: string, author_name: string, author_email: string, iso_date: string, relative_date: string, message: string}|null
     */
    public function getLatestCommit(): ?array
    {
        $result = $this->executeCommand([
            'git', 'log', '-n', '1', '--pretty=format:%H|%h|%an|%ae|%aI|%ar|%s',
        ]);

        if (! $result['success'] || empty(trim($result['output']))) {
            return null;
        }

        $parts = explode('|', trim($result['output']), 7);

        if (count($parts) < 7) {
            return null;
        }

        return [
            'hash' => $parts[0],
            'short_hash' => $parts[1],
            'author_name' => $parts[2],
            'author_email' => $parts[3],
            'iso_date' => $parts[4],
            'relative_date' => $parts[5],
            'message' => $parts[6],
        ];
    }

    /**
     * Get upstream tracking branch and ahead/behind sync counts.
     *
     * @return array{upstream: ?string, ahead: int, behind: int}
     */
    public function getSyncStatus(): array
    {
        $upstreamRes = $this->executeCommand(['git', 'rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}']);
        $upstream = $upstreamRes['success'] ? trim($upstreamRes['output']) : null;

        if (empty($upstream)) {
            return [
                'upstream' => null,
                'ahead' => 0,
                'behind' => 0,
            ];
        }

        $countRes = $this->executeCommand(['git', 'rev-list', '--left-right', '--count', 'HEAD...@{u}']);

        $ahead = 0;
        $behind = 0;

        if ($countRes['success'] && ! empty(trim($countRes['output']))) {
            $counts = preg_split('/\s+/', trim($countRes['output']));
            if (isset($counts[0])) {
                $ahead = (int) $counts[0];
            }
            if (isset($counts[1])) {
                $behind = (int) $counts[1];
            }
        }

        return [
            'upstream' => $upstream,
            'ahead' => $ahead,
            'behind' => $behind,
        ];
    }

    /**
     * Get working tree uncommitted changes status.
     *
     * @return array{is_clean: bool, total_changes: int, files: array<int, array{file: string, status: string, label: string, badge_class: string}>, counts: array{modified: int, added: int, deleted: int, untracked: int}}
     */
    public function getWorkingTreeStatus(): array
    {
        $result = $this->executeCommand(['git', 'status', '--porcelain']);

        if (! $result['success']) {
            return [
                'is_clean' => true,
                'total_changes' => 0,
                'files' => [],
                'counts' => ['modified' => 0, 'added' => 0, 'deleted' => 0, 'untracked' => 0],
            ];
        }

        $rawLines = array_filter(explode("\n", trim($result['output'])));
        $files = [];
        $counts = ['modified' => 0, 'added' => 0, 'deleted' => 0, 'untracked' => 0];

        foreach ($rawLines as $line) {
            if (strlen($line) < 3) {
                continue;
            }

            $statusCode = substr($line, 0, 2);
            $fileName = trim(substr($line, 3));

            $status = 'modified';
            $label = 'Modified';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';

            if (str_contains($statusCode, '?')) {
                $status = 'untracked';
                $label = 'Untracked';
                $badgeClass = 'bg-sky-50 text-sky-700 border-sky-200';
                $counts['untracked']++;
            } elseif (str_contains($statusCode, 'A')) {
                $status = 'added';
                $label = 'Added';
                $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                $counts['added']++;
            } elseif (str_contains($statusCode, 'D')) {
                $status = 'deleted';
                $label = 'Deleted';
                $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                $counts['deleted']++;
            } else {
                $counts['modified']++;
            }

            $files[] = [
                'file' => $fileName,
                'status' => $status,
                'label' => $label,
                'badge_class' => $badgeClass,
            ];
        }

        return [
            'is_clean' => count($files) === 0,
            'total_changes' => count($files),
            'files' => $files,
            'counts' => $counts,
        ];
    }

    /**
     * Get recent commit logs.
     *
     * @return array<int, array{hash: string, short_hash: string, author_name: string, author_email: string, iso_date: string, relative_date: string, message: string}>
     */
    public function getRecentCommits(int $limit = 15): array
    {
        $result = $this->executeCommand([
            'git', 'log', "-n", (string) $limit, '--pretty=format:%H|%h|%an|%ae|%aI|%ar|%s',
        ]);

        if (! $result['success'] || empty(trim($result['output']))) {
            return [];
        }

        $lines = array_filter(explode("\n", trim($result['output'])));
        $commits = [];

        foreach ($lines as $line) {
            $parts = explode('|', $line, 7);
            if (count($parts) === 7) {
                $commits[] = [
                    'hash' => $parts[0],
                    'short_hash' => $parts[1],
                    'author_name' => $parts[2],
                    'author_email' => $parts[3],
                    'iso_date' => $parts[4],
                    'relative_date' => $parts[5],
                    'message' => $parts[6],
                ];
            }
        }

        return $commits;
    }

    /**
     * Get local and remote branch names.
     *
     * @return array{local: array<int, string>, remote: array<int, string>}
     */
    public function getBranches(): array
    {
        $result = $this->executeCommand(['git', 'branch', '-a', '--no-color']);

        $local = [];
        $remote = [];

        if ($result['success']) {
            $lines = array_filter(explode("\n", trim($result['output'])));

            foreach ($lines as $line) {
                $clean = trim(ltrim($line, '* '));

                if (str_contains($clean, '->')) {
                    continue;
                }

                if (str_starts_with($clean, 'remotes/')) {
                    $remoteBranch = preg_replace('#^remotes/(origin/)?#', '', $clean);
                    if ($remoteBranch && ! in_array($remoteBranch, $remote, true)) {
                        $remote[] = $remoteBranch;
                    }
                } else {
                    $local[] = $clean;
                }
            }
        }

        return [
            'local' => array_values(array_unique($local)),
            'remote' => array_values(array_unique($remote)),
        ];
    }

    /**
     * Get list of stashes.
     *
     * @return array<int, string>
     */
    public function getStashes(): array
    {
        $result = $this->executeCommand(['git', 'stash', 'list']);

        if (! $result['success'] || empty(trim($result['output']))) {
            return [];
        }

        return array_values(array_filter(explode("\n", trim($result['output']))));
    }

    /**
     * Comprehensive summary of repo information for UI rendering.
     *
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        $isInstalled = $this->isGitInstalled();
        $isRepo = $isInstalled && $this->isRepository();

        if (! $isRepo) {
            return [
                'is_installed' => $isInstalled,
                'git_version' => $this->getGitVersion(),
                'is_repository' => false,
                'branch' => 'None',
                'remote_url' => null,
                'web_url' => null,
                'latest_commit' => null,
                'sync' => ['upstream' => null, 'ahead' => 0, 'behind' => 0],
                'working_tree' => ['is_clean' => true, 'total_changes' => 0, 'files' => [], 'counts' => ['modified' => 0, 'added' => 0, 'deleted' => 0, 'untracked' => 0]],
                'recent_commits' => [],
                'branches' => ['local' => [], 'remote' => []],
                'stashes' => [],
                'diagnostics' => self::SAFE_DIAGNOSTICS,
            ];
        }

        $remoteUrl = $this->getRemoteUrl();

        return [
            'is_installed' => true,
            'git_version' => $this->getGitVersion(),
            'is_repository' => true,
            'branch' => $this->getCurrentBranch(),
            'remote_url' => $remoteUrl,
            'web_url' => $this->getWebUrl($remoteUrl),
            'latest_commit' => $this->getLatestCommit(),
            'sync' => $this->getSyncStatus(),
            'working_tree' => $this->getWorkingTreeStatus(),
            'recent_commits' => $this->getRecentCommits(15),
            'branches' => $this->getBranches(),
            'stashes' => $this->getStashes(),
            'diagnostics' => self::SAFE_DIAGNOSTICS,
        ];
    }

    /**
     * Fetch from remote.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function fetch(string $remote = 'origin'): array
    {
        return $this->executeCommand(['git', 'fetch', $remote, '--prune'], timeout: 60);
    }

    /**
     * Pull updates from remote repository.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function pull(
        string $remote = 'origin',
        ?string $branch = null,
        bool $runMigrations = false,
        bool $clearCache = false
    ): array {
        $targetBranch = $branch ?: $this->getCurrentBranch();
        $result = $this->executeCommand(['git', 'pull', $remote, $targetBranch], timeout: 120);

        $appendedOutput = $result['output'];

        if ($result['success'] && $runMigrations) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrationOutput = trim(Artisan::output());
                $appendedOutput .= "\n\n[php artisan migrate --force]\n".$migrationOutput;
            } catch (\Throwable $e) {
                $appendedOutput .= "\n\n[php artisan migrate --force ERROR]\n".$e->getMessage();
            }
        }

        if ($result['success'] && $clearCache) {
            try {
                Artisan::call('optimize:clear');
                $cacheOutput = trim(Artisan::output());
                $appendedOutput .= "\n\n[php artisan optimize:clear]\n".$cacheOutput;
            } catch (\Throwable $e) {
                $appendedOutput .= "\n\n[php artisan optimize:clear ERROR]\n".$e->getMessage();
            }
        }

        $result['output'] = trim($appendedOutput);

        return $result;
    }

    /**
     * Switch / checkout branch.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function checkout(string $branch): array
    {
        $cleanBranch = trim($branch);

        if (! preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $cleanBranch)) {
            return [
                'success' => false,
                'command' => 'git checkout',
                'output' => '',
                'error' => 'Invalid branch name supplied.',
                'exit_code' => 1,
                'duration_ms' => 0.0,
            ];
        }

        return $this->executeCommand(['git', 'checkout', $cleanBranch], timeout: 60);
    }

    /**
     * Stash working directory changes.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function stash(string $message = ''): array
    {
        $cmd = ['git', 'stash', 'push'];
        if (! empty(trim($message))) {
            $cmd[] = '-m';
            $cmd[] = trim($message);
        }

        return $this->executeCommand($cmd, timeout: 60);
    }

    /**
     * Pop the most recent stash.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function stashPop(): array
    {
        return $this->executeCommand(['git', 'stash', 'pop'], timeout: 60);
    }

    /**
     * Discard all uncommitted working directory changes.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function discardChanges(): array
    {
        $restore = $this->executeCommand(['git', 'restore', '.'], timeout: 60);

        if (! $restore['success']) {
            $restore = $this->executeCommand(['git', 'checkout', '--', '.'], timeout: 60);
        }

        return $restore;
    }

    /**
     * Stage all changes, create a commit, and optionally push to remote.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function commitAndPush(
        string $message,
        bool $push = false,
        string $remote = 'origin',
        ?string $branch = null
    ): array {
        $cleanMessage = trim($message);

        if (empty($cleanMessage)) {
            return [
                'success' => false,
                'command' => 'git commit',
                'output' => '',
                'error' => 'Commit message is required.',
                'exit_code' => 1,
                'duration_ms' => 0.0,
            ];
        }

        $addRes = $this->executeCommand(['git', 'add', '-A'], timeout: 60);
        if (! $addRes['success']) {
            return $addRes;
        }

        $commitRes = $this->executeCommand(['git', 'commit', '-m', $cleanMessage], timeout: 60);
        if (! $commitRes['success']) {
            return $commitRes;
        }

        $output = $commitRes['output'];

        if ($push) {
            $targetBranch = $branch ?: $this->getCurrentBranch();
            $pushRes = $this->executeCommand(['git', 'push', $remote, $targetBranch], timeout: 120);

            $output .= "\n\n[git push {$remote} {$targetBranch}]\n".($pushRes['output'] ?: $pushRes['error']);

            if (! $pushRes['success']) {
                return [
                    'success' => false,
                    'command' => "git push {$remote} {$targetBranch}",
                    'output' => $output,
                    'error' => $pushRes['error'] ?: 'Git push failed. Verify remote write credentials or merge upstream.',
                    'exit_code' => $pushRes['exit_code'],
                    'duration_ms' => $commitRes['duration_ms'] + $pushRes['duration_ms'],
                ];
            }
        }

        return [
            'success' => true,
            'command' => 'git commit '.($push ? '& push' : ''),
            'output' => trim($output),
            'error' => '',
            'exit_code' => 0,
            'duration_ms' => $commitRes['duration_ms'],
        ];
    }

    /**
     * Run a whitelisted diagnostic command safely.
     *
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    public function runDiagnostic(string $key): array
    {
        if (! isset(self::SAFE_DIAGNOSTICS[$key])) {
            return [
                'success' => false,
                'command' => 'diagnostic',
                'output' => '',
                'error' => "Unknown diagnostic command key [{$key}].",
                'exit_code' => 1,
                'duration_ms' => 0.0,
            ];
        }

        $spec = self::SAFE_DIAGNOSTICS[$key];

        return $this->executeCommand($spec['cmd'], timeout: 45);
    }

    /**
     * Execute a process in the repository working directory.
     *
     * @param array<int, string> $command
     * @return array{success: bool, command: string, output: string, error: string, exit_code: int, duration_ms: float}
     */
    private function executeCommand(array $command, float $timeout = 30): array
    {
        $startTime = microtime(true);

        try {
            $process = new Process($command, base_path(), null, null, $timeout);
            $process->run();

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
            $exitCode = $process->getExitCode() ?? 1;

            return [
                'success' => $process->isSuccessful(),
                'command' => implode(' ', $command),
                'output' => trim($output),
                'error' => trim($errorOutput),
                'exit_code' => $exitCode,
                'duration_ms' => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => false,
                'command' => implode(' ', $command),
                'output' => '',
                'error' => $e->getMessage(),
                'exit_code' => 1,
                'duration_ms' => $duration,
            ];
        }
    }
}
