<x-app-layout>
    <x-slot name="title">Mis anuncios — Coches.app</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Mis anuncios</h1>
            <a href="{{ route('coches.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + Publicar anuncio
            </a>
        </div>

        @if($coches->count())
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Anuncio</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Visitas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($coches as $coche)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-[260px]">
                                        <img src="{{ $coche->imagenPrincipal?->url ?? 'https://loremflickr.com/120/90/car?lock='.$coche->id }}"
                                             class="shrink-0 w-16 h-12 object-cover rounded">
                                        <div class="min-w-0">
                                            <a href="{{ route('coches.show', $coche) }}"
                                               class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                                {{ $coche->title }}
                                            </a>
                                            <div class="text-xs text-gray-500">{{ $coche->year }} · {{ number_format($coche->mileage_km, 0, ',', '.') }} km</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $coche->status->badgeClasses() }}">
                                        {{ $coche->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-price-format :value="$coche->price" class="font-semibold text-gray-900" />
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $coche->views_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('coches.edit', $coche) }}"
                                           class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>

                                        @if($coche->status->value === 'active')
                                            <form method="POST" action="{{ route('coches.mark-sold', $coche) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">Vendido</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('coches.destroy', $coche) }}"
                                              onsubmit="return confirm('¿Eliminar este anuncio?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-sm text-red-600 hover:text-red-800 font-medium">Borrar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
              </div>
            </div>

            <div class="mt-6">{{ $coches->links() }}</div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg p-10 text-center">
                <p class="text-gray-700">Aún no has publicado ningún anuncio.</p>
                <a href="{{ route('coches.create') }}"
                   class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Publicar el primero
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
