<x-guest-layout>
    <x-slot name="title">Recuperar contraseña — Carros.net</x-slot>

    <h1 class="text-xl font-bold text-gray-900 mb-1">Recuperar contraseña</h1>
    <p class="text-sm text-gray-500 mb-6">
        ¿Olvidaste tu contraseña? Sin problema. Indícanos tu correo electrónico y te enviaremos un enlace para restablecerla.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-5">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                ← Volver al acceso
            </a>
            <x-primary-button>
                Enviar enlace
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
