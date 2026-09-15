<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;

class MaintenanceService
{
    /**
     * @var array<string, array{command: string, arguments: array<string, mixed>, label: string}>
     */
    private const array ACTIONS = [
        'all-clear' => ['command' => 'optimize:clear', 'arguments' => [], 'label' => 'All system caches successfully cleared (Cache, Views, Routes, Config)'],
        'cache-clear' => ['command' => 'cache:clear', 'arguments' => [], 'label' => 'Application data cache successfully cleared'],
        'view-clear' => ['command' => 'view:clear', 'arguments' => [], 'label' => 'Compiled Blade views cache successfully cleared'],
        'route-clear' => ['command' => 'route:clear', 'arguments' => [], 'label' => 'Route URL cache successfully cleared'],
        'config-clear' => ['command' => 'config:clear', 'arguments' => [], 'label' => 'Configuration cache successfully cleared'],
        'migrate' => ['command' => 'migrate', 'arguments' => ['--force' => true], 'label' => 'Database migrations ran successfully'],
        'storage-link' => ['command' => 'storage:link', 'arguments' => [], 'label' => 'Public storage symlink created successfully'],
    ];

    /**
     * @return array{label: string, output: string}
     */
    public function run(string $action): array
    {
        if (! isset(self::ACTIONS[$action])) {
            throw new InvalidArgumentException("Unknown maintenance action [{$action}].");
        }

        ['command' => $command, 'arguments' => $arguments, 'label' => $label] = self::ACTIONS[$action];

        Artisan::call($command, $arguments);

        return ['label' => $label, 'output' => trim(Artisan::output())];
    }

    /**
     * @return array<string, string>
     */
    public function availableActions(): array
    {
        return array_map(fn (array $action) => $action['label'], self::ACTIONS);
    }
}
