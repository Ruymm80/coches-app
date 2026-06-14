<?php

namespace App\Services;

/**
 * Descarga una imagen para un coche. Estrategia:
 *   1. Pixabay (si hay PIXABAY_API_KEY) → fotos reales por marca + modelo
 *   2. Fallback: loremflickr → fotos de Flickr por tag de marca
 *
 * Devuelve siempre ['data' => bytes, 'mime_type' => string] o null si todo falla.
 */
class CarImageDownloader
{
    public function __construct(
        protected PixabayImageDownloader $pixabay,
        protected SeedImageDownloader $loremflickr,
    ) {}

    public function fetch(string $brand, ?string $model = null, int $seed = 0): ?array
    {
        // 1) Pixabay (mejor calidad si está habilitado)
        if ($this->pixabay->isEnabled()) {
            $result = $this->pixabay->download($brand, $model);
            if ($result) {
                return $result;
            }
        }

        // 2) Fallback: loremflickr con tag de marca
        return $this->loremflickr->download($brand, $seed ?: random_int(1, 100000));
    }
}
