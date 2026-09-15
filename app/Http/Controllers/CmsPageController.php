<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function show(Page $page): View
    {
        abort_unless($page->status->value === 'published', 404);

        return view('pages.cms-page', [
            'page' => $page,
        ]);
    }
}
