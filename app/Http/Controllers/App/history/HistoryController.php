<?php

namespace App\Http\Controllers\App\history;

use App\Services\TopupService;

class HistoryController
{
    public function __construct(private readonly TopupService $topupService) {}

    public function index()
    {
        return view('components.pages.app.history.history-index');
    }
}
