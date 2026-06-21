<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\about;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    /**
     * Hiển thị trang giới thiệu (About Page) của ứng dụng NDHGift.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        return view('components.pages.app.about.about-index');
    }
}
