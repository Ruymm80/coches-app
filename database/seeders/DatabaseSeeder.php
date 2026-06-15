<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Chat;
use App\Models\Coche;
use App\Models\Favorito;
use App\Models\Imagen;
use App\Models\Mensaje;
use App\Models\User;
use App\Services\SeedImageDownloader;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(SeedImageDownloader $downloader): void
    {
        // Idempotente: si el admin ya existe, asumimos que la BD ya está sembrada.
        // Esto evita el "UniqueConstraintViolationException" al reiniciar el contenedor
        // en producción (Railway, Heroku, etc.) donde el seed corre al arrancar.
        if (User::where('email', 'admin@coches.test')->exists()) {
            $this->command->info('BD ya sembrada (admin@coches.test existe). Saltando seed.');
            return;
        }

        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@coches.test',
            'province' => 'Madrid',
        ]);

        $demo = User::factory()->create([
            'name' => 'Usuario Demo',
            'email' => 'user@coches.test',
            'role' => Role::User,
            'province' => 'Barcelona',
        ]);

        $users = User::factory(20)->create();
        $allSellers = $users->push($demo);

        $totalCoches = 100;
        $this->command->info("Descargando imágenes (puede tardar 1-3 minutos)...");
        $bar = $this->command->getOutput()->createProgressBar($totalCoches);
        $bar->start();

        for ($i = 0; $i < $totalCoches; $i++) {
            $seller = $allSellers->random();

            $coche = Coche::factory()
                ->for($seller)
                ->create();

            // 70% de los anuncios → 1 imagen (mejor coherencia portada/contenido).
            // 30% restante → 2-4 imágenes para poder mostrar el carrusel.
            $imageCount = rand(1, 10) <= 7 ? 1 : rand(2, 4);

            for ($j = 0; $j < $imageCount; $j++) {
                $seed = ($coche->id * 1000) + ($j * 100) + rand(0, 99);
                $download = $downloader->download($coche->brand, $seed);

                if ($download === null) {
                    // Fallback: URL externa si la descarga falla
                    $tag = Str::slug(explode('-', explode(' ', $coche->brand)[0])[0]) ?: 'car';
                    Imagen::create([
                        'coche_id' => $coche->id,
                        'path' => "https://loremflickr.com/800/600/{$tag}?lock={$seed}",
                        'sort_order' => $j,
                        'is_primary' => $j === 0,
                    ]);
                } else {
                    Imagen::create([
                        'coche_id' => $coche->id,
                        'data' => $download['data'],
                        'mime_type' => $download['mime_type'],
                        'sort_order' => $j,
                        'is_primary' => $j === 0,
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);

        $activeCoches = Coche::active()->get();

        $allSellers->each(function (User $user) use ($activeCoches) {
            $sampleSize = min(8, $activeCoches->count() - 1);
            $activeCoches
                ->where('user_id', '!=', $user->id)
                ->random($sampleSize)
                ->each(function (Coche $coche) use ($user) {
                    Favorito::firstOrCreate([
                        'user_id' => $user->id,
                        'coche_id' => $coche->id,
                    ]);
                });
        });

        $chatCount = min(20, $activeCoches->count());
        $sampleCoches = $activeCoches->random($chatCount);

        foreach ($sampleCoches as $coche) {
            $buyer = $allSellers->where('id', '!=', $coche->user_id)->random();

            $chat = Chat::firstOrCreate([
                'coche_id' => $coche->id,
                'buyer_id' => $buyer->id,
            ], [
                'seller_id' => $coche->user_id,
            ]);

            Mensaje::factory()->count(rand(2, 5))->create([
                'chat_id' => $chat->id,
                'sender_id' => fake()->randomElement([$buyer->id, $coche->user_id]),
            ]);
        }

        $this->command->info("Admin:  admin@coches.test / password");
        $this->command->info("User:   user@coches.test  / password");
        $this->command->info("Total coches: ".Coche::count());
    }
}
