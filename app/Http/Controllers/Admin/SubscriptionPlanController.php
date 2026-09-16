<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.subscription-plans.index', [
            'salerPlans' => SubscriptionPlan::query()->forType(SubscriptionType::Saler)->orderBy('sort_order')->get(),
            'businessPlans' => SubscriptionPlan::query()->forType(SubscriptionType::Business)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreSubscriptionPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (SubscriptionPlan::query()->max('sort_order') ?? 0) + 1;

        SubscriptionPlan::query()->create($data);

        return back()->with('status', 'Plan created.');
    }

    public function update(StoreSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $data = $request->validated();
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $subscriptionPlan->update($data);

        return back()->with('status', 'Plan updated.');
    }

    public function toggleActive(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->update(['is_active' => ! $subscriptionPlan->is_active]);

        $status = $subscriptionPlan->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Plan {$status}.");
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->delete();

        return back()->with('status', 'Plan removed.');
    }
}
