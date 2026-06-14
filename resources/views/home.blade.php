<x-app-layout>
    <x-slot name="title">Carros.net — Compra y vende coches de segunda mano</x-slot>

    <section class="relative bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white overflow-hidden">
        <div class="absolute inset-0 opacity-20 pointer-events-none"
             style="background-image: radial-gradient(circle at 25% 30%, rgba(255,255,255,.5) 0, transparent 40%), radial-gradient(circle at 75% 70%, rgba(255,255,255,.3) 0, transparent 40%);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight max-w-3xl leading-tight">
                Encuentra el coche perfecto para ti
            </h1>
            <p class="mt-4 text-base sm:text-lg text-indigo-100 max-w-2xl">
                Miles de anuncios de particulares. Filtra por marca, precio, kilómetros y mucho más.
            </p>

            <form method="GET" action="{{ route('coches.index') }}"
                  class="mt-8 bg-white rounded-xl p-2 sm:p-3 flex flex-col sm:flex-row gap-2 shadow-2xl max-w-3xl">
                <input type="text" name="q" placeholder="Ej: BMW Serie 3, Tesla Model 3..."
                       class="flex-1 rounded-lg border-0 text-gray-900 focus:ring-2 focus:ring-indigo-500 text-sm sm:text-base px-3">
                <input type="number" name="price_max" placeholder="Precio máx €"
                       class="sm:w-44 rounded-lg border-0 text-gray-900 focus:ring-2 focus:ring-indigo-500 text-sm sm:text-base px-3">
                <button type="submit"
                        class="px-6 py-3 sm:py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                    Buscar
                </button>
            </form>

            <div class="mt-6 flex flex-wrap gap-2">
                <span class="text-xs text-indigo-200 uppercase tracking-wide self-center mr-2">Populares:</span>
                @foreach(['Audi', 'BMW', 'Mercedes-Benz', 'Volkswagen', 'SEAT', 'Toyota'] as $brand)
                    <a href="{{ route('coches.index', ['brand' => $brand]) }}"
                       class="inline-flex items-center px-3 py-1 text-xs font-medium bg-white/10 hover:bg-white/20 backdrop-blur rounded-full transition">
                        {{ $brand }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if($featured->count())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Anuncios destacados</h2>
                    <p class="text-sm text-gray-500 mt-1">Las mejores ofertas seleccionadas</p>
                </div>
                <a href="{{ route('coches.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 whitespace-nowrap">
                    Ver todos →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featured as $coche)
                    <x-coche-card :coche="$coche" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Recién publicados</h2>
                <p class="text-sm text-gray-500 mt-1">Lo último que ha llegado al mercado</p>
            </div>
            <a href="{{ route('coches.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 whitespace-nowrap">
                Ver todos →
            </a>
        </div>

        @if($recent->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recent as $coche)
                    <x-coche-card :coche="$coche" />
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <p class="text-gray-500">Aún no hay anuncios publicados.</p>
            </div>
        @endif
    </section>
</x-app-layout>
