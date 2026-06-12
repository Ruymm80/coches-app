<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Descarga una imagen de loremflickr para un coche y la guarda en
 * storage/app/public/coches/{id}/{n}.jpg. Devuelve la ruta relativa
 * que se guarda en imagenes.path.
 *
 * Si la descarga falla, devuelve null para que el seeder use un placeholder.
 */
class SeedImageDownloader
{
    /** Mapa de marca → tag de Flickr (normalizado). */
    protected array $brandTagMap = [
        'Audi' => 'audi',
        'BMW' => 'bmw',
        'Mercedes-Benz' => 'mercedes',
        'Volkswagen' => 'volkswagen',
        'SEAT' => 'seat',
        'Renault' => 'renault',
        'Peugeot' => 'peugeot',
        'Citroën' => 'citroen',
        'Ford' => 'ford',
        'Toyota' => 'toyota',
        'Hyundai' => 'hyundai',
        'Kia' => 'kia',
    ];

    public function download(int $cocheId, string $brand, int $index, int $seed): ?string
    {
        $tag = $this->brandTagMap[$brand] ?? Str::slug($brand);
        $url = "https://loremflickr.com/800/600/{$tag}?lock={$seed}";

        try {
            $response = Http::timeout(20)
                ->withoutVerifying()  // evita problemas de CA cert en Windows
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (! $response->successful() || strlen($response->body()) < 5000) {
                return null;
            }

            $path = "coches/{$cocheId}/{$index}.jpg";
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
