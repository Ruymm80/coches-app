@php
    $links = [
        ['Dashboard', 'admin.dashboard'],
        ['Usuarios', 'admin.users.index'],
        ['Anuncios', 'admin.listings.index'],
    ];
@endphp

<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-1 overflow-x-auto">
        @foreach($links as [$label, $name])
            <a href="{{ route($name) }}"
               class="px-4 py-3 text-sm font-medium border-b-2 -mb-px whitespace-nowrap
                   {{ request()->routeIs($name) || request()->routeIs(str_replace('.index', '.*', $name))
                       ? 'border-indigo-600 text-indigo-600'
                       : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>
