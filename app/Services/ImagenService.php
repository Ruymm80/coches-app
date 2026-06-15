<?php

namespace App\Services;

use App\Models\Coche;
use App\Models\Imagen;
use Illuminate\Http\UploadedFile;

/**
 * Servicio responsable de la galería de imágenes de un coche: subir, borrar
 * y mantener consistente la imagen principal. Centraliza esa lógica para que
 * los controladores no manipulen el modelo Imagen directamente.
 */
class ImagenService
{
    /**
     * Guarda las imágenes subidas como BLOB en la BD. La primera imagen del
     * anuncio se marca automáticamente como principal si todavía no había una.
     */
    public function storeFor(Coche $coche, array $files): void
    {
        $start = $coche->imagenes()->max('sort_order');
        $next = is_null($start) ? 0 : $start + 1;

        $hasPrimary = $coche->imagenes()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $isPrimary = ! $hasPrimary;
            $hasPrimary = true;

            Imagen::create([
                'coche_id'   => $coche->id,
                'data'       => file_get_contents($file->getRealPath()),
                'mime_type'  => $file->getMimeType() ?: 'image/jpeg',
                'sort_order' => $next++,
                'is_primary' => $isPrimary,
            ]);
        }
    }

    /**
     * Borra imágenes específicas del coche. Al estar en BD, basta con eliminar
     * las filas. Si la imagen principal se borra, asciende la siguiente.
     */
    public function deleteForCoche(Coche $coche, array $imagenIds): void
    {
        $imagenes = $coche->imagenes()->whereIn('id', $imagenIds)->get();
        $deletedPrimary = $imagenes->contains(fn ($i) => $i->is_primary);

        Imagen::whereIn('id', $imagenes->pluck('id'))->delete();

        if ($deletedPrimary) {
            $next = $coche->imagenes()->orderBy('sort_order')->first();
            $next?->update(['is_primary' => true]);
        }
    }

    /** Borra todas las imágenes del coche (cuando se elimina el anuncio). */
    public function deleteAllForCoche(Coche $coche): void
    {
        $coche->imagenes()->delete();
    }
}
