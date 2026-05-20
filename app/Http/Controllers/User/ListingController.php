<?php

namespace App\Http\Controllers\User;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListingController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(Request $request)
    {
        $listings = $request->user()
            ->listings()
            ->with('primaryImage')
            ->latest()
            ->paginate(10);

        return view('account.listings.index', compact('listings'));
    }

    public function create()
    {
        return view('account.listings.create', [
            'listing' => new Listing(['status' => ListingStatus::Draft->value]),
        ]);
    }

    public function store(StoreListingRequest $request)
    {
        $data = $request->validated();
        $images = $request->file('images', []);
        unset($data['images']);

        $listing = $request->user()->listings()->create($data);

        if (! empty($images)) {
            $this->images->storeFor($listing, $images);
        }

        return redirect()
            ->route('account.listings.index')
            ->with('status', 'Anuncio creado correctamente.');
    }

    public function edit(Listing $listing)
    {
        Gate::authorize('update', $listing);

        $listing->loadMissing('images');

        return view('account.listings.edit', compact('listing'));
    }

    public function update(UpdateListingRequest $request, Listing $listing)
    {
        $data = $request->validated();
        $images = $request->file('images', []);
        $deleteIds = $data['delete_images'] ?? [];
        unset($data['images'], $data['delete_images']);

        $listing->update($data);

        if (! empty($deleteIds)) {
            $this->images->deleteForListing($listing, $deleteIds);
        }

        if (! empty($images)) {
            $this->images->storeFor($listing, $images);
        }

        return redirect()
            ->route('account.listings.edit', $listing)
            ->with('status', 'Anuncio actualizado.');
    }

    public function destroy(Listing $listing)
    {
        Gate::authorize('delete', $listing);

        $this->images->deleteAllForListing($listing);
        $listing->delete();

        return redirect()
            ->route('account.listings.index')
            ->with('status', 'Anuncio eliminado.');
    }

    public function markSold(Listing $listing)
    {
        Gate::authorize('update', $listing);

        $listing->update(['status' => ListingStatus::Sold]);

        return back()->with('status', 'Anuncio marcado como vendido.');
    }
}
