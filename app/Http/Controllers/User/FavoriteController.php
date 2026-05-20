<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $listings = $request->user()
            ->favoriteListings()
            ->with('primaryImage')
            ->latest('favorites.created_at')
            ->paginate(12);

        return view('account.favorites', compact('listings'));
    }

    public function toggle(Request $request, Listing $listing)
    {
        $user = $request->user();
        $existing = $user->favorites()->where('listing_id', $listing->id)->first();

        if ($existing) {
            $existing->delete();
            $message = 'Eliminado de favoritos.';
        } else {
            $user->favorites()->create(['listing_id' => $listing->id]);
            $message = 'Añadido a favoritos.';
        }

        return back()->with('status', $message);
    }
}
