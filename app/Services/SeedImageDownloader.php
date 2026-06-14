<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Descarga una imagen de loremflickr para un coche y devuelve el binario
 * junto al mime-type, para almacenarse como BLOB en la BD.
 *
 * Si la descarga falla, devuelve null para que el seeder use un placeholder.
 */
class SeedImageDownloader
{
    /** Mapa marca → tag de Flickr normalizado. */
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

    /**
     * Devuelve ['data' => bytes, 'mime_type' => string] o null si falla.
     */
    public function download(string $brand, int $seed): ?array
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

            return [
                'data' => $response->body(),
                'mime_type' => $response->header('Content-Type') ?: 'image/jpeg',
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
