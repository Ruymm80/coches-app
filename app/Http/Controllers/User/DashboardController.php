<?php

namespace App\Http\Controllers\User;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total' => $user->listings()->count(),
            'active' => $user->listings()->where('status', ListingStatus::Active)->count(),
            'sold' => $user->listings()->where('status', ListingStatus::Sold)->count(),
            'draft' => $user->listings()->where('status', ListingStatus::Draft)->count(),
            'views' => (int) $user->listings()->sum('views_count'),
            'favorites' => $user->favorites()->count(),
        ];

        $latest = $user->listings()
            ->with('primaryImage')
            ->latest()
            ->take(5)
            ->get();

        return view('account.dashboard', compact('stats', 'latest'));
    }
}
