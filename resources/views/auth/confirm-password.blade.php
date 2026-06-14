<x-guest-layout>
    <x-slot name="title">Confirmar contraseña — Carros.net</x-slot>

    <h1 class="text-xl font-bold text-gray-900 mb-1">Confirmar contraseña</h1>
    <p class="text-sm text-gray-500 mb-6">
        Esta es una zona protegida de la aplicación. Por favor, confirma tu contraseña antes de continuar.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-5">
            <x-primary-button>
                Confirmar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
