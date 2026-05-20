<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
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

        $totalListings = 100;
        for ($i = 0; $i < $totalListings; $i++) {
            $seller = $allSellers->random();

            $listing = Listing::factory()
                ->for($seller)
                ->create();

            $imageCount = rand(1, 4);
            $brandTag = $this->brandToTag($listing->brand);
            $modelTag = Str::slug($listing->model);

            for ($j = 0; $j < $imageCount; $j++) {
                $seed = rand(1, 100000);
                $tags = $modelTag !== '' ? "{$brandTag},{$modelTag},car" : "{$brandTag},car";

                ListingImage::factory()
                    ->for($listing)
                    ->state([
                        'sort_order' => $j,
                        'is_primary' => $j === 0,
                        'path' => "https://loremflickr.com/800/600/{$tags}/all?lock={$seed}",
                    ])
                    ->create();
            }
        }

        $activeListings = Listing::active()->get();

        $allSellers->each(function (User $user) use ($activeListings) {
            $sampleSize = min(8, $activeListings->count() - 1);
            $activeListings
                ->where('user_id', '!=', $user->id)
                ->random($sampleSize)
                ->each(function (Listing $listing) use ($user) {
                    Favorite::firstOrCreate([
                        'user_id' => $user->id,
                        'listing_id' => $listing->id,
                    ]);
                });
        });

        $conversationCount = min(20, $activeListings->count());
        $sampleListings = $activeListings->random($conversationCount);

        foreach ($sampleListings as $listing) {
            $buyer = $allSellers->where('id', '!=', $listing->user_id)->random();

            $conversation = Conversation::firstOrCreate([
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
            ], [
                'seller_id' => $listing->user_id,
            ]);

            Message::factory()->count(rand(2, 5))->create([
                'conversation_id' => $conversation->id,
                'sender_id' => fake()->randomElement([$buyer->id, $listing->user_id]),
            ]);
        }

        $this->command->info("Admin:  admin@coches.test / password");
        $this->command->info("User:   user@coches.test  / password");
        $this->command->info("Total anuncios: ".Listing::count());
    }

    protected function brandToTag(string $brand): string
    {
        $first = explode('-', explode(' ', trim($brand))[0])[0];

        return Str::slug($first) ?: 'car';
    }
}
