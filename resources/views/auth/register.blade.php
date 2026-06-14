<x-guest-layout>
    <x-slot name="title">Crear cuenta — Carros.net</x-slot>

    <h1 class="text-xl font-bold text-gray-900 mb-1">Crear una cuenta</h1>
    <p class="text-sm text-gray-500 mb-6">Publica tus coches y contacta con otros usuarios en minutos.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4">
            <div>
                <x-input-label for="phone" value="Teléfono (opcional)" />
                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="province" value="Provincia (opcional)" />
                <x-text-input id="province" class="block mt-1 w-full" type="text" name="province" :value="old('province')" autocomplete="address-level1" />
                <x-input-error :messages="$errors->get('province')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                ¿Ya tienes cuenta?
            </a>

            <x-primary-button class="ms-4">
                Crear cuenta
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
