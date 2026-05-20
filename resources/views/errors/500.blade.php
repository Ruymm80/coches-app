<x-app-layout>
    <x-slot name="title">Error del servidor — Coches.app</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <p class="text-7xl font-extrabold text-rose-600">500</p>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Algo ha fallado</h1>
        <p class="mt-2 text-gray-600">Lo sentimos, ha ocurrido un error inesperado. Inténtalo de nuevo en unos minutos.</p>

        <div class="mt-6">
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                Volver al inicio
            </a>
        </div>
    </div>
</x-app-layout>
