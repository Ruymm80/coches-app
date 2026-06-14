<x-guest-layout>
    <x-slot name="title">Verificar correo — Carros.net</x-slot>

    <h1 class="text-xl font-bold text-gray-900 mb-1">Verifica tu correo</h1>
    <p class="text-sm text-gray-500 mb-6">
        Gracias por registrarte. Antes de continuar, por favor verifica tu correo haciendo clic en el enlace que te acabamos de enviar.
        Si no lo recibes, puedes pedir uno nuevo abajo.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Se ha enviado un nuevo enlace de verificación al correo indicado durante el registro.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                Reenviar correo de verificación
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>
