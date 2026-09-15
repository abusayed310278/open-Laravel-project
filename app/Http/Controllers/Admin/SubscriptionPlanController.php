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
        SubscriptionPlan::query()->create([
            ...$request->validated(),
            'sort_order' => SubscriptionPlan::query()->max('sort_order') + 1,
        ]);

        return back()->with('status', 'Plan created.');
    }

    public function update(StoreSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->update($request->validated());

        return back()->with('status', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->delete();

        return back()->with('status', 'Plan removed.');
    }
}
