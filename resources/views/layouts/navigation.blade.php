<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-600 text-white font-bold">C</span>
                        <span class="font-bold text-lg">Coches<span class="text-indigo-600">.app</span></span>
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        Inicio
                    </x-nav-link>
                    <x-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.*')">
                        Buscar coches
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                @auth
                    <a href="{{ route('account.listings.create') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                        + Publicar anuncio
                    </a>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none">
                                <div>{{ Auth::user()->name }}</div>
                                <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('account.dashboard')">Mi cuenta</x-dropdown-link>
                            @if (Auth::user()->isAdmin())
                                <x-dropdown-link :href="route('admin.dashboard')">Panel admin</x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('account.listings.index')">Mis anuncios</x-dropdown-link>
                            <x-dropdown-link :href="route('account.favorites.index')">Mis favoritos</x-dropdown-link>
                            <x-dropdown-link :href="route('account.messages.index')">
                                Mensajes
                                @if(($unreadMessages ?? 0) > 0)
                                    <span class="ms-1 inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-500 text-white">{{ $unreadMessages }}</span>
                                @endif
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    Cerrar sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">Acceder</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                        Registrarse
                    </a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.*')">Buscar coches</x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('account.dashboard')">Mi cuenta</x-responsive-nav-link>
                    @if (Auth::user()->isAdmin())
                        <x-responsive-nav-link :href="route('admin.dashboard')">Panel admin</x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link :href="route('account.listings.create')">Publicar anuncio</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('account.listings.index')">Mis anuncios</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('account.favorites.index')">Mis favoritos</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('account.messages.index')">
                        Mensajes
                        @if(($unreadMessages ?? 0) > 0)
                            <span class="ms-1 inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-500 text-white">{{ $unreadMessages }}</span>
                        @endif
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('profile.edit')">Perfil</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            Cerrar sesión
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="px-4 space-y-1">
                    <x-responsive-nav-link :href="route('login')">Acceder</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">Registrarse</x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
