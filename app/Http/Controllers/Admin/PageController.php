<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search'));

        $pages = Page::query()
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('meta_title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->orderBy('id', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.index', [
            'pages' => $pages,
            'statuses' => ContentStatus::cases(),
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
        Cache::forget('published_page_slugs');

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
        Cache::forget('published_page_slugs');

        return redirect()->route('admin.pages.index')->with('status', 'Page updated.');
    }

    public function toggleStatus(Page $page): RedirectResponse|JsonResponse
    {
        $newStatus = $page->status === ContentStatus::Published ? ContentStatus::Draft : ContentStatus::Published;
        $page->update(['status' => $newStatus]);
        Cache::forget('published_page_slugs');

        $actionText = $newStatus === ContentStatus::Published ? 'activated' : 'deactivated';

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus->value,
                'label' => $newStatus->label(),
                'badgeColor' => $newStatus->badgeColor(),
                'isPublished' => $newStatus === ContentStatus::Published,
                'message' => "Page '{$page->title}' {$actionText}.",
            ]);
        }

        return back()->with('status', "Page '{$page->title}' {$actionText}.");
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();
        Cache::forget('published_page_slugs');

        return back()->with('status', 'Page deleted.');
    }
}
