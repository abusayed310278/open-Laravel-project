<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new Page,
            'statuses' => ContentStatus::cases(),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        Page::create($request->validated());

        return redirect()->route('admin.pages.index')->with('status', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'statuses' => ContentStatus::cases(),
        ]);
    }

    public function update(StorePageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->validated());

        return redirect()->route('admin.pages.index')->with('status', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return back()->with('status', 'Page deleted.');
    }
}
