<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public string $title;

    public bool $hideFooterMobile;

    public bool $hideHeaderMobile;

    public function __construct(string $title = 'NDHGift', bool $hideFooterMobile = false, bool $hideHeaderMobile = false)
    {
        $this->title = $title;
        $this->hideFooterMobile = $hideFooterMobile;
        $this->hideHeaderMobile = $hideHeaderMobile;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app.app-layout');
    }
}
