@props(['listing'])

@php
    $img = $listing->primaryImage?->url
        ?? $listing->images->first()?->url
        ?? 'https://loremflickr.com/800/600/car,automobile/all?lock=0';
@endphp

<a href="{{ route('listings.show', $listing) }}"
   class="group block bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
    <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
        <img src="{{ $img }}"
             alt="{{ $listing->title }}"
             class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
             loading="lazy">

        @if($listing->featured)
            <span class="absolute top-2 left-2 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded">
                Destacado
            </span>
        @endif

        @if($listing->status->value !== 'active')
            <span class="absolute top-2 right-2 text-xs font-semibold px-2 py-1 rounded {{ $listing->status->badgeClasses() }}">
                {{ $listing->status->label() }}
            </span>
        @endif
    </div>

    <div class="p-4">
        <h3 class="font-semibold text-gray-900 truncate group-hover:text-indigo-600">
            {{ $listing->title }}
        </h3>

        <div class="mt-1 text-sm text-gray-500 flex items-center gap-x-2 flex-wrap">
            <span>{{ $listing->year }}</span>
            <span>·</span>
            <span>{{ number_format($listing->mileage_km, 0, ',', '.') }} km</span>
            <span>·</span>
            <span>{{ $listing->fuel_type->label() }}</span>
        </div>

        <div class="mt-3 flex items-center justify-between">
            <x-price-format :value="$listing->price" class="text-lg font-bold text-indigo-600" />
            <span class="text-xs text-gray-500">{{ $listing->province }}</span>
        </div>
    </div>
</a>
