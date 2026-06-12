@props(['coche'])

@php
    $img = $coche->imagenPrincipal?->url
        ?? $coche->imagenes->first()?->url
        ?? 'https://loremflickr.com/800/600/car?lock='.$coche->id;
@endphp

<a href="{{ route('coches.show', $coche) }}"
   class="group flex flex-col bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
    <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
        <img src="{{ $img }}"
             alt="{{ $coche->title }}"
             class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
             loading="lazy"
             onerror="this.onerror=null;this.src='https://loremflickr.com/800/600/car?lock={{ $coche->id }}'">

        @if($coche->featured)
            <span class="absolute top-2 left-2 inline-flex items-center gap-1 bg-amber-500 text-white text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded shadow-sm">
                ★ Destacado
            </span>
        @endif

        @if($coche->status->value !== 'active')
            <span class="absolute top-2 right-2 text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded {{ $coche->status->badgeClasses() }}">
                {{ $coche->status->label() }}
            </span>
        @endif
    </div>

    <div class="p-4 flex flex-col flex-1 min-w-0">
        <h3 class="font-semibold text-gray-900 truncate group-hover:text-indigo-600">
            {{ $coche->title }}
        </h3>

        <div class="mt-1 text-xs text-gray-500 flex items-center gap-x-2 gap-y-1 flex-wrap">
            <span>{{ $coche->year }}</span>
            <span class="text-gray-300">·</span>
            <span>{{ number_format($coche->mileage_km, 0, ',', '.') }} km</span>
            <span class="text-gray-300">·</span>
            <span>{{ $coche->fuel_type->label() }}</span>
        </div>

        <div class="mt-auto pt-3 flex items-end justify-between gap-2">
            <x-price-format :value="$coche->price" class="text-lg font-bold text-indigo-600 whitespace-nowrap" />
            <span class="text-xs text-gray-500 truncate">{{ $coche->province }}</span>
        </div>
    </div>
</a>
