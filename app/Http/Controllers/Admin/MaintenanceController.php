<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\Admin\MaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceService $maintenance) {}

    public function index(): View
    {
        return view('admin.settings.system', [
            'actions' => $this->maintenance->availableActions(),
        ]);
    }

    public function run(string $action): RedirectResponse
    {
        try {
            $result = $this->maintenance->run($action);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        ActivityLog::record('settings.maintenance.'.$action, properties: ['output' => $result['output']]);
        Log::info("Admin maintenance action run: {$action}", $result);

        return back()->with('status', $result['label'].'.');
    }
}
