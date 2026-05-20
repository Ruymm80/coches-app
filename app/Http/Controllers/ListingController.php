<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Support\ListingFilter;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request, ListingFilter $filter)
    {
        $listings = $filter->apply(
            Listing::active()->with('primaryImage')
        )->paginate(12)->withQueryString();

        $provinces = Listing::active()
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        return view('listings.index', [
            'listings' => $listings,
            'provinces' => $provinces,
            'filters' => $request->query(),
            'sorts' => ListingFilter::SORTS,
        ]);
    }

    public function show(Listing $listing)
    {
        abort_unless($listing->status->value === 'active' || $this->canPreview($listing), 404);

        $listing->loadMissing(['images', 'user']);
        $listing->increment('views_count');

        $similar = Listing::active()
            ->with('primaryImage')
            ->where('id', '!=', $listing->id)
            ->where('brand', $listing->brand)
            ->latest()
            ->take(4)
            ->get();

        return view('listings.show', compact('listing', 'similar'));
    }

    protected function canPreview(Listing $listing): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->id === $listing->user_id);
    }
}
