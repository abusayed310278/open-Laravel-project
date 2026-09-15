<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommissionRuleType;
use App\Enums\CommissionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCommissionRuleRequest;
use App\Models\ActivityLog;
use App\Models\CommissionRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommissionRuleController extends Controller
{
    public function index(): View
    {
        return view('admin.commission-rules.index', [
            'rules' => CommissionRule::query()->orderByDesc('priority')->latest()->get(),
            'types' => CommissionRuleType::cases(),
            'commissionTypes' => CommissionType::cases(),
        ]);
    }

    public function store(StoreCommissionRuleRequest $request): RedirectResponse
    {
        $rule = CommissionRule::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'priority' => $request->integer('priority', 0),
        ]);

        ActivityLog::record('commission-rule.created', $rule);

        return back()->with('status', 'Commission rule created.');
    }

    public function update(StoreCommissionRuleRequest $request, CommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'priority' => $request->integer('priority', 0),
        ]);

        ActivityLog::record('commission-rule.updated', $commissionRule);

        return back()->with('status', 'Commission rule updated.');
    }

    public function destroy(CommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->delete();

        return back()->with('status', 'Commission rule deleted.');
    }
}
