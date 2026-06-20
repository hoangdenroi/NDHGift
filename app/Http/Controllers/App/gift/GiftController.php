<?php

namespace App\Http\Controllers\App\gift;

use App\Http\Controllers\Controller;

class GiftController extends Controller
{
    public function index()
    {
        return view('components.pages.app.gift.gift-index');
    }
}
