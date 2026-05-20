<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ListingController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');

        $listings = Listing::query()
            ->with(['user', 'primaryImage'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                       ->orWhere('brand', 'like', "%{$q}%")
                       ->orWhere('model', 'like', "%{$q}%");
                });
            })
            ->when($status && ListingStatus::tryFrom($status), fn ($qq) => $qq->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.listings.index', [
            'listings' => $listings,
            'q' => $q,
            'status' => $status,
            'statuses' => collect(ListingStatus::cases())
                ->mapWithKeys(fn ($c) => [$c->value => $c->label()]),
        ]);
    }

    public function updateStatus(Request $request, Listing $listing)
    {
        $data = $request->validate([
            'status' => ['required', new Enum(ListingStatus::class)],
        ]);

        $listing->update($data);

        return back()->with('status', 'Estado actualizado a '.$listing->status->label().'.');
    }

    public function toggleFeatured(Listing $listing)
    {
        $listing->update(['featured' => ! $listing->featured]);

        return back()->with('status', $listing->featured ? 'Anuncio destacado.' : 'Quitado de destacados.');
    }

    public function destroy(Listing $listing)
    {
        $this->images->deleteAllForListing($listing);
        $listing->delete();

        return redirect()
            ->route('admin.listings.index')
            ->with('status', 'Anuncio eliminado.');
    }
}
