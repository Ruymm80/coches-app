<?php

namespace App\Http\Controllers;

use App\Models\Listing;

class HomeController extends Controller
{
    public function __invoke()
    {
        $featured = Listing::active()
            ->with(['primaryImage'])
            ->where('featured', true)
            ->latest()
            ->take(6)
            ->get();

        $recent = Listing::active()
            ->with(['primaryImage'])
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('featured', 'recent'));
    }
}
