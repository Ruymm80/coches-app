<x-app-layout>
    <x-slot name="title">Mis favoritos — Coches.app</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Mis favoritos</h1>

        @if($coches->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($coches as $coche)
                    <div class="relative">
                        <x-coche-card :coche="$coche" />
                        <form method="POST" action="{{ route('coches.favorite', $coche) }}"
                              class="absolute top-2 right-2">
                            @csrf
                            <button class="bg-white/90 hover:bg-white text-rose-600 text-xs font-medium px-2 py-1 rounded shadow">
                                ♥ Quitar
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $coches->links() }}</div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                <div class="text-5xl mb-3">♡</div>
                <p class="text-gray-900 font-medium">Aún no has guardado favoritos</p>
                <p class="text-sm text-gray-500 mt-1">Marca con corazón los coches que más te gusten para verlos aquí.</p>
                <a href="{{ route('coches.index') }}"
                   class="mt-5 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Explorar coches
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
