<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Carros.net') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('logo.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 mb-6">
                <img src="{{ asset('logo.svg') }}" alt="Carros.net" class="shrink-0 w-10 h-10 object-contain">
                <span class="font-bold text-2xl text-gray-900">Carros<span class="text-indigo-600">.net</span></span>
            </a>

            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
