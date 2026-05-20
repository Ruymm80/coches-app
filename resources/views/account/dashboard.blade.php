<x-app-layout>
    <x-slot name="title">Mi cuenta — Coches.app</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Hola, {{ auth()->user()->name }}</h1>
                <p class="text-sm text-gray-500">Esta es tu cuenta personal en Coches.app.</p>
            </div>
            <a href="{{ route('account.listings.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + Publicar anuncio
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
            @foreach([
                ['Total', $stats['total']],
                ['Activos', $stats['active']],
                ['Vendidos', $stats['sold']],
                ['Borradores', $stats['draft']],
                ['Visitas', $stats['views']],
                ['Favoritos', $stats['favorites']],
            ] as [$label, $value])
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="font-semibold text-gray-900">Mis últimos anuncios</h2>
                <a href="{{ route('account.listings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    Ver todos →
                </a>
            </div>

            @if($latest->count())
                <ul class="divide-y">
                    @foreach($latest as $listing)
                        <li class="p-4 flex items-center gap-4">
                            <img src="{{ $listing->primaryImage?->url ?? $listing->images->first()?->url ?? 'https://loremflickr.com/200/150/car,automobile/all?lock=0' }}"
                                 class="w-20 h-16 object-cover rounded">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('listings.show', $listing) }}" class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                    {{ $listing->title }}
                                </a>
                                <div class="text-xs text-gray-500">
                                    {{ $listing->views_count }} visitas ·
                                    <span class="px-1.5 py-0.5 rounded text-[10px] {{ $listing->status->badgeClasses() }}">
                                        {{ $listing->status->label() }}
                                    </span>
                                </div>
                            </div>
                            <x-price-format :value="$listing->price" class="font-semibold text-indigo-600" />
                            <a href="{{ route('account.listings.edit', $listing) }}"
                               class="text-sm text-gray-700 hover:text-indigo-600 font-medium">Editar</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-8 text-center text-gray-500">
                    <p>Aún no has publicado anuncios.</p>
                    <a href="{{ route('account.listings.create') }}" class="mt-3 inline-block text-indigo-600 font-medium hover:text-indigo-800">
                        Publicar el primero →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
