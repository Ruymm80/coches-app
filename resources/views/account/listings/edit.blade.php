<x-app-layout>
    <x-slot name="title">Editar anuncio — Coches.app</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('account.listings.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Volver a mis anuncios</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Editar anuncio</h1>
            </div>
            <a href="{{ route('listings.show', $listing) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver en público →</a>
        </div>

        <form method="POST" action="{{ route('account.listings.update', $listing) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('account.listings._form', ['listing' => $listing, 'submitLabel' => 'Guardar cambios'])
        </form>
    </div>
</x-app-layout>
