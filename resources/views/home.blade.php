<x-app-layout>
    <x-slot name="title">Coches.app — Compra y vende coches de segunda mano</x-slot>

    <section class="bg-gradient-to-br from-indigo-600 to-indigo-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-3xl sm:text-5xl font-bold tracking-tight max-w-3xl">
                Encuentra el coche perfecto para ti
            </h1>
            <p class="mt-4 text-indigo-100 max-w-2xl">
                Miles de anuncios de particulares. Filtra por marca, precio, km y mucho más.
            </p>

            <form method="GET" action="{{ route('listings.index') }}"
                  class="mt-8 bg-white rounded-lg p-3 sm:p-4 flex flex-col sm:flex-row gap-2 shadow-lg max-w-3xl">
                <input type="text" name="q" placeholder="Ej: BMW Serie 3, Tesla Model 3..."
                       class="flex-1 rounded-md border-gray-300 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                <input type="number" name="price_max" placeholder="Precio máx €"
                       class="sm:w-40 rounded-md border-gray-300 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">
                    Buscar
                </button>
            </form>
        </div>
    </section>

    @if($featured->count())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-end justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Anuncios destacados</h2>
                <a href="{{ route('listings.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    Ver todos →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featured as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="flex items-end justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Recién publicados</h2>
            <a href="{{ route('listings.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Ver todos →
            </a>
        </div>

        @if($recent->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recent as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <p class="text-gray-500">Aún no hay anuncios publicados.</p>
            </div>
        @endif
    </section>
</x-app-layout>
