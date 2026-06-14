<x-app-layout>
    <x-slot name="title">Mi cuenta — Carros.net</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Hola, {{ auth()->user()->name }}</h1>
                <p class="text-sm text-gray-500">Esta es tu cuenta personal en Carros.net.</p>
            </div>
            <a href="{{ route('coches.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + Publicar anuncio
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
            @foreach([
                ['Total', $stats['total'], 'text-gray-900'],
                ['Activos', $stats['active'], 'text-green-600'],
                ['Vendidos', $stats['sold'], 'text-blue-600'],
                ['Borradores', $stats['draft'], 'text-gray-500'],
                ['Visitas', $stats['views'], 'text-indigo-600'],
                ['Favoritos', $stats['favoritos'], 'text-rose-600'],
            ] as [$label, $value, $color])
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-sm transition">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</div>
                    <div class="mt-1 text-2xl font-bold {{ $color }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="font-semibold text-gray-900">Mis últimos anuncios</h2>
                <a href="{{ route('coches.mine') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    Ver todos →
                </a>
            </div>

            @if($latest->count())
                <ul class="divide-y">
                    @foreach($latest as $coche)
                        <li class="p-4 flex items-center gap-4">
                            <img src="{{ $coche->imagenPrincipal?->url ?? $coche->imagenes->first()?->url ?? 'https://loremflickr.com/200/150/car?lock='.$coche->id }}"
                                 class="shrink-0 w-20 h-16 object-cover rounded">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('coches.show', $coche) }}" class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                    {{ $coche->title }}
                                </a>
                                <div class="text-xs text-gray-500 flex items-center gap-2 flex-wrap mt-0.5">
                                    <span>{{ $coche->views_count }} visitas</span>
                                    <span class="text-gray-300">·</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] {{ $coche->status->badgeClasses() }}">
                                        {{ $coche->status->label() }}
                                    </span>
                                </div>
                            </div>
                            <x-price-format :value="$coche->price" class="shrink-0 font-semibold text-indigo-600 whitespace-nowrap" />
                            <a href="{{ route('coches.edit', $coche) }}"
                               class="shrink-0 text-sm text-gray-700 hover:text-indigo-600 font-medium">Editar</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-8 text-center text-gray-500">
                    <p>Aún no has publicado anuncios.</p>
                    <a href="{{ route('coches.create') }}" class="mt-3 inline-block text-indigo-600 font-medium hover:text-indigo-800">
                        Publicar el primero →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
