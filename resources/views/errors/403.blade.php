<x-app-layout>
    <x-slot name="title">Acceso denegado — Coches.app</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <p class="text-7xl font-extrabold text-rose-600">403</p>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">No tienes permiso</h1>
        <p class="mt-2 text-gray-600">
            {{ $exception->getMessage() ?: 'No estás autorizado para acceder a esta página.' }}
        </p>

        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                Volver al inicio
            </a>
            @auth
                <a href="{{ route('account.dashboard') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    Mi cuenta
                </a>
            @endauth
        </div>
    </div>
</x-app-layout>
