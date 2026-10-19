<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVerificationChecklistRequest;
use App\Models\Category;
use App\Models\VerificationChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationChecklistController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search'));

        $items = VerificationChecklist::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('item_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.verification-checklists.index', [
            'items' => $items,
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreVerificationChecklistRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_required'] = $request->boolean('is_required');
        $data['sort_order'] = (VerificationChecklist::query()->max('sort_order') ?? 0) + 1;

        VerificationChecklist::query()->create($data);

        return back()->with('status', 'Checklist item added.');
    }

    public function update(StoreVerificationChecklistRequest $request, VerificationChecklist $verificationChecklist): RedirectResponse
    {
        $data = $request->validated();
        $data['is_required'] = $request->boolean('is_required');

        $verificationChecklist->update($data);

        return back()->with('status', 'Checklist item updated.');
    }

    public function toggleRequired(VerificationChecklist $verificationChecklist): RedirectResponse
    {
        $verificationChecklist->update(['is_required' => ! $verificationChecklist->is_required]);

        $status = $verificationChecklist->is_required ? 'marked as required' : 'marked as optional';

        return back()->with('status', "Checklist item {$status}.");
    }

    public function destroy(VerificationChecklist $verificationChecklist): RedirectResponse
    {
        $verificationChecklist->delete();

        return back()->with('status', 'Checklist item removed.');
    }
}
