<x-app-layout>
    <x-slot name="title">Panel admin — Coches.app</x-slot>

    @include('admin._nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Panel de administración</h1>

        @php
            $colors = [
                'gray' => 'text-gray-900',
                'indigo' => 'text-indigo-600',
                'green' => 'text-green-600',
                'blue' => 'text-blue-600',
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            @foreach([
                ['Usuarios', $stats['users_total'], 'gray'],
                ['Admins', $stats['users_admins'], 'indigo'],
                ['Anuncios', $stats['coches_total'], 'gray'],
                ['Activos', $stats['coches_active'], 'green'],
                ['Vendidos', $stats['coches_sold'], 'blue'],
                ['Borradores', $stats['coches_draft'], 'gray'],
                ['Conversaciones', $stats['chats'], 'gray'],
                ['Mensajes', $stats['mensajes'], 'gray'],
            ] as [$label, $value, $color])
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</div>
                    <div class="mt-1 text-2xl font-bold {{ $colors[$color] }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg">
                <div class="p-4 border-b flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Últimos anuncios</h2>
                    <a href="{{ route('admin.coches.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todos →</a>
                </div>
                <ul class="divide-y">
                    @foreach($recentCoches as $coche)
                        <li class="p-3 flex items-center gap-3">
                            <img src="{{ $coche->imagenPrincipal?->url ?? 'https://loremflickr.com/100/75/car?lock='.$coche->id }}"
                                 class="shrink-0 w-14 h-11 object-cover rounded">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('coches.show', $coche) }}"
                                   class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                    {{ $coche->title }}
                                </a>
                                <div class="text-xs text-gray-500 truncate">{{ $coche->user->name }}</div>
                            </div>
                            <span class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded {{ $coche->status->badgeClasses() }}">
                                {{ $coche->status->label() }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg">
                <div class="p-4 border-b flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Últimos usuarios</h2>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todos →</a>
                </div>
                <ul class="divide-y">
                    @foreach($recentUsers as $user)
                        <li class="p-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 truncate">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded
                                {{ $user->role->value === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $user->role->label() }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
