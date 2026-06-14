<x-guest-layout>
    <x-slot name="title">Acceder — Carros.net</x-slot>

    <h1 class="text-xl font-bold text-gray-900 mb-1">Bienvenido de nuevo</h1>
    <p class="text-sm text-gray-500 mb-6">Accede para gestionar tus anuncios y conversaciones.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Recuérdame</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-5">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Acceder
            </x-primary-button>
        </div>

        <p class="mt-6 text-sm text-gray-600 text-center">
            ¿Aún no tienes cuenta?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Regístrate</a>
        </p>
    </form>
</x-guest-layout>
