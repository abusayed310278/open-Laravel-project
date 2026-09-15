<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVerificationChecklistRequest;
use App\Models\Category;
use App\Models\VerificationChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationChecklistController extends Controller
{
    public function index(): View
    {
        return view('admin.verification-checklists.index', [
            'items' => VerificationChecklist::query()->with('category')->orderBy('sort_order')->get(),
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreVerificationChecklistRequest $request): RedirectResponse
    {
        VerificationChecklist::query()->create([
            ...$request->validated(),
            'sort_order' => VerificationChecklist::query()->max('sort_order') + 1,
        ]);

        return back()->with('status', 'Checklist item added.');
    }

    public function destroy(VerificationChecklist $verificationChecklist): RedirectResponse
    {
        $verificationChecklist->delete();

        return back()->with('status', 'Checklist item removed.');
    }
}
