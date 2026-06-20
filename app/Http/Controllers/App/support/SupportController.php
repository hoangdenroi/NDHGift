<?php

namespace App\Http\Controllers\App\support;

use App\Http\Controllers\Controller;

class SupportController extends Controller
{
    public function index()
    {
        return view('components.pages.app.support.support-index');
    }
}
