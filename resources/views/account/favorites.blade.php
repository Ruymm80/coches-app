<x-app-layout>
    <x-slot name="title">Mis favoritos — Coches.app</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Mis favoritos</h1>

        @if($listings->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($listings as $listing)
                    <div class="relative">
                        <x-listing-card :listing="$listing" />
                        <form method="POST" action="{{ route('listings.favorite', $listing) }}"
                              class="absolute top-2 right-2">
                            @csrf
                            <button class="bg-white/90 hover:bg-white text-rose-600 text-xs font-medium px-2 py-1 rounded shadow">
                                ♥ Quitar
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $listings->links() }}</div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg p-10 text-center">
                <p class="text-gray-700">Aún no has guardado favoritos.</p>
                <a href="{{ route('listings.index') }}"
                   class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Explorar coches
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
