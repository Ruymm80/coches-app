<?php

namespace App\Services;

use App\Models\Coche;
use App\Models\Imagen;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImagenService
{
    public function storeFor(Coche $coche, array $files): void
    {
        $start = $coche->imagenes()->max('sort_order');
        $next = is_null($start) ? 0 : $start + 1;

        $hasPrimary = $coche->imagenes()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store("coches/{$coche->id}", 'public');

            $isPrimary = ! $hasPrimary;
            $hasPrimary = true;

            Imagen::create([
                'coche_id' => $coche->id,
                'path' => $path,
                'sort_order' => $next++,
                'is_primary' => $isPrimary,
            ]);
        }
    }

    public function deleteForCoche(Coche $coche, array $imagenIds): void
    {
        $imagenes = $coche->imagenes()->whereIn('id', $imagenIds)->get();

        $deletedPrimary = false;

        foreach ($imagenes as $imagen) {
            if ($imagen->is_primary) {
                $deletedPrimary = true;
            }
            if (! str_starts_with($imagen->path, 'http')) {
                Storage::disk('public')->delete($imagen->path);
            }
            $imagen->delete();
        }

        if ($deletedPrimary) {
            $next = $coche->imagenes()->orderBy('sort_order')->first();
            $next?->update(['is_primary' => true]);
        }
    }

    public function deleteAllForCoche(Coche $coche): void
    {
        foreach ($coche->imagenes as $imagen) {
            if (! str_starts_with($imagen->path, 'http')) {
                Storage::disk('public')->delete($imagen->path);
            }
        }

        Storage::disk('public')->deleteDirectory("coches/{$coche->id}");
    }
}
