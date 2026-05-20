<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'users_total' => User::count(),
            'users_admins' => User::where('role', Role::Admin)->count(),
            'listings_total' => Listing::count(),
            'listings_active' => Listing::where('status', ListingStatus::Active)->count(),
            'listings_sold' => Listing::where('status', ListingStatus::Sold)->count(),
            'listings_draft' => Listing::where('status', ListingStatus::Draft)->count(),
            'conversations' => Conversation::count(),
            'messages' => Message::count(),
        ];

        $recentListings = Listing::with(['user', 'primaryImage'])
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentListings', 'recentUsers'));
    }
}
