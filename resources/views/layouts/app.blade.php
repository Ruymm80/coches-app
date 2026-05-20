<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Coches App') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white border-b">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                @if (session('status'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="bg-white border-t mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-500 flex flex-col sm:flex-row justify-between gap-2">
                    <div>&copy; {{ date('Y') }} Coches App — Proyecto DAW</div>
                    <div class="flex gap-4">
                        <a href="{{ route('listings.index') }}" class="hover:text-gray-900">Buscar coches</a>
                        @auth
                            <a href="{{ route('profile.edit') }}" class="hover:text-gray-900">Mi cuenta</a>
                        @else
                            <a href="{{ route('login') }}" class="hover:text-gray-900">Acceder</a>
                        @endauth
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
