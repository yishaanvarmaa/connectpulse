<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home');
    }

    public function pricing(): View
    {
        return view('marketing.pricing', [
            'plans' => config('connectpulse.pricing'),
        ]);
    }

    public function contact(): View
    {
        return view('marketing.contact');
    }

    public function privacy(): View
    {
        return view('marketing.privacy');
    }

    public function terms(): View
    {
        return view('marketing.terms');
    }

    public function refund(): View
    {
        return view('marketing.refund');
    }
}
