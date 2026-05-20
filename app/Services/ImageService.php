<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function storeFor(Listing $listing, array $files): void
    {
        $start = $listing->images()->max('sort_order');
        $next = is_null($start) ? 0 : $start + 1;

        $hasPrimary = $listing->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store("listings/{$listing->id}", 'public');

            $isPrimary = ! $hasPrimary;
            $hasPrimary = true;

            ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $path,
                'sort_order' => $next++,
                'is_primary' => $isPrimary,
            ]);
        }
    }

    public function deleteForListing(Listing $listing, array $imageIds): void
    {
        $images = $listing->images()->whereIn('id', $imageIds)->get();

        $deletedPrimary = false;

        foreach ($images as $image) {
            if ($image->is_primary) {
                $deletedPrimary = true;
            }
            if (! str_starts_with($image->path, 'http')) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }

        if ($deletedPrimary) {
            $next = $listing->images()->orderBy('sort_order')->first();
            $next?->update(['is_primary' => true]);
        }
    }

    public function deleteAllForListing(Listing $listing): void
    {
        foreach ($listing->images as $image) {
            if (! str_starts_with($image->path, 'http')) {
                Storage::disk('public')->delete($image->path);
            }
        }

        Storage::disk('public')->deleteDirectory("listings/{$listing->id}");
    }
}
