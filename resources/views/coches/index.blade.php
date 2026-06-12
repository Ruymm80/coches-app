<x-app-layout>
    <x-slot name="title">Coches en venta — Coches.app</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Coches en venta</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $coches->total() }} {{ Str::plural('anuncio', $coches->total()) }} encontrado{{ $coches->total() === 1 ? '' : 's' }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <aside class="lg:col-span-1 lg:sticky lg:top-20 lg:self-start">
                <x-coche-filtros :filters="$filters" :provinces="$provinces" :sorts="$sorts" />
            </aside>

            <section class="lg:col-span-3">
                @if($coches->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                        @foreach($coches as $coche)
                            <x-coche-card :coche="$coche" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $coches->links() }}
                    </div>
                @else
                    <div class="text-center py-16 bg-white rounded-lg border border-gray-200">
                        <p class="text-gray-700 font-medium">No hay coches que coincidan con tu búsqueda.</p>
                        <p class="text-sm text-gray-500 mt-1">Prueba a ampliar los filtros.</p>
                        <a href="{{ route('coches.index') }}"
                           class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Limpiar filtros
                        </a>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
