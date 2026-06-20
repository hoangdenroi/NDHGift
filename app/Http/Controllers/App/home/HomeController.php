<?php

namespace App\Http\Controllers\App\home;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('components.pages.app.home.home-index');
    }
}