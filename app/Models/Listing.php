<?php

namespace App\Models;

use App\Enums\BodyType;
use App\Enums\FuelType;
use App\Enums\ListingStatus;
use App\Enums\Transmission;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'title', 'slug', 'brand', 'model', 'description',
    'price', 'year', 'mileage_km', 'fuel_type', 'transmission',
    'body_type', 'color', 'province', 'status', 'featured',
])]
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fuel_type' => FuelType::class,
            'transmission' => Transmission::class,
            'body_type' => BodyType::class,
            'status' => ListingStatus::class,
            'featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            if (blank($listing->slug)) {
                $listing->slug = static::generateUniqueSlug($listing->title);
            }
        });

        static::updating(function (Listing $listing) {
            if ($listing->isDirty('title') && ! $listing->isDirty('slug')) {
                $listing->slug = static::generateUniqueSlug($listing->title, $listing->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ListingImage::class)->where('is_primary', true);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ListingStatus::Active);
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}
