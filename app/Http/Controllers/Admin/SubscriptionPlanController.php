<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search'));

        $salerPlans = SubscriptionPlan::query()
            ->forType(SubscriptionType::Saler)
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->get();

        $businessPlans = SubscriptionPlan::query()
            ->forType(SubscriptionType::Business)
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->get();

        return view('admin.subscription-plans.index', [
            'salerPlans' => $salerPlans,
            'businessPlans' => $businessPlans,
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
        try {
            if ($subscriptionPlan->subscriptions()->exists()) {
                $subscriptionPlan->update(['is_active' => false]);

                return back()->with('status', 'Plan has existing subscriber history, so it was deactivated to preserve user records instead of deleted.');
            }

            $subscriptionPlan->delete();

            return back()->with('status', 'Plan removed.');
        } catch (\Throwable $e) {
            $subscriptionPlan->update(['is_active' => false]);

            return back()->with('status', 'Plan deactivated (cannot delete plan linked to active records).');
        }
    }
}
