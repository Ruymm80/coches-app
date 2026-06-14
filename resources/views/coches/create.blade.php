<x-app-layout>
    <x-slot name="title">Publicar anuncio — Carros.net</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('coches.mine') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Volver a mis anuncios</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Publicar nuevo anuncio</h1>
        </div>

        <form method="POST" action="{{ route('coches.store') }}" enctype="multipart/form-data">
            @csrf
            @include('coches._form', ['listing' => $coche, 'submitLabel' => 'Publicar anuncio'])
        </form>
    </div>
</x-app-layout>
