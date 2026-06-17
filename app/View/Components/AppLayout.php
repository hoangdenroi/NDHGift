<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public string $title;

    public bool $hideFooterMobile;

    public function __construct(string $title = 'NDHGift', bool $hideFooterMobile = false)
    {
        $this->title = $title;
        $this->hideFooterMobile = $hideFooterMobile;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app.app-layout');
    }
}
