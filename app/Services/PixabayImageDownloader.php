<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Descarga una foto real desde la API de Pixabay buscando "{marca} {modelo} car".
 * Requiere PIXABAY_API_KEY en .env. Sin key, devuelve null.
 *
 * https://pixabay.com/api/docs/
 */
class PixabayImageDownloader
{
    public function __construct(protected ?string $apiKey = null)
    {
        $this->apiKey = $this->apiKey ?: config('services.pixabay.key');
    }

    public function isEnabled(): bool
    {
        return ! empty($this->apiKey);
    }

    public function download(string $brand, ?string $model = null): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $query = trim($brand.' '.$model.' car');

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->get('https://pixabay.com/api/', [
                    'key' => $this->apiKey,
                    'q' => $query,
                    'image_type' => 'photo',
                    'category' => 'transportation',
                    'orientation' => 'horizontal',
                    'per_page' => 20,
                    'safesearch' => 'true',
                ]);

            if (! $response->successful()) {
                return null;
            }

            $hits = $response->json('hits', []);

            // Fallback: si no hay resultados para "marca + modelo", probar solo con la marca
            if (empty($hits) && $model) {
                return $this->download($brand, null);
            }

            if (empty($hits)) {
                return null;
            }

            $hit = $hits[array_rand($hits)];
            $imageUrl = $hit['webformatURL'] ?? $hit['previewURL'] ?? null;

            if (! $imageUrl) {
                return null;
            }

            $img = Http::timeout(20)->withoutVerifying()->get($imageUrl);

            if (! $img->successful() || strlen($img->body()) < 3000) {
                return null;
            }

            return [
                'data' => $img->body(),
                'mime_type' => $img->header('Content-Type') ?: 'image/jpeg',
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
