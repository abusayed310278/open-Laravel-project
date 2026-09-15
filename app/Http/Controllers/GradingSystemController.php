<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GradingSystemController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.grading-system');
    }
}
