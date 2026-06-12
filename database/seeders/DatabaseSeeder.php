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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(SeedImageDownloader $downloader): void
    {
        Storage::disk('public')->deleteDirectory('coches');

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

            $imageCount = rand(1, 3);
            for ($j = 0; $j < $imageCount; $j++) {
                $seed = ($coche->id * 100) + $j + rand(0, 999);
                $path = $downloader->download($coche->id, $coche->brand, $j, $seed);

                if ($path === null) {
                    $tag = Str::slug(explode('-', explode(' ', $coche->brand)[0])[0]) ?: 'car';
                    $path = "https://loremflickr.com/800/600/{$tag}?lock={$seed}";
                }

                Imagen::create([
                    'coche_id' => $coche->id,
                    'path' => $path,
                    'sort_order' => $j,
                    'is_primary' => $j === 0,
                ]);
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
