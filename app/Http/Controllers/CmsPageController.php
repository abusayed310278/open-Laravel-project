<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function show(Page $page): View|RedirectResponse
    {
        abort_unless($page->status->value === 'published', 404);

        if ($page->slug === 'blog') {
            return redirect()->route('blog.index');
        }

        return view('pages.cms-page', [
            'page' => $page,
        ]);
    }
}
