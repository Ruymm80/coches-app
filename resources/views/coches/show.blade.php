<x-app-layout>
    <x-slot name="title">{{ $coche->title }} — Carros.net</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Inicio</a>
            <span class="mx-1">/</span>
            <a href="{{ route('coches.index') }}" class="hover:text-indigo-600">Coches</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $coche->title }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                @php
                    $imagenesJson = $coche->imagenes->map(fn ($i) => $i->url)->toJson();
                    $totalImagenes = $coche->imagenes->count();
                @endphp

                <div x-data="{
                        active: 0,
                        images: {{ $imagenesJson }},
                        count: {{ $totalImagenes }},
                        next() { this.active = (this.active + 1) % this.count; },
                        prev() { this.active = (this.active - 1 + this.count) % this.count; },
                     }"
                     @keydown.left.window="count > 1 && prev()"
                     @keydown.right.window="count > 1 && next()"
                     class="bg-white rounded-lg border border-gray-200 overflow-hidden">

                    {{-- Carrusel principal (estilo Bootstrap) --}}
                    <div class="relative aspect-[4/3] bg-gray-100 group">
                        @if($totalImagenes)
                            <template x-for="(img, idx) in images" :key="idx">
                                <img :src="img"
                                     x-show="active === idx"
                                     x-transition:enter="transition-opacity duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     class="absolute inset-0 w-full h-full object-cover" />
                            </template>
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                Sin imágenes
                            </div>
                        @endif

                        @if($coche->featured)
                            <span class="absolute top-3 left-3 z-10 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded shadow">
                                ★ Destacado
                            </span>
                        @endif

                        {{-- Overlays del carrusel (contador, flechas, indicadores) --}}
                        @if($totalImagenes > 1)
                            {{-- Contador 3 / 8 (arriba a la derecha) --}}
                            <span class="absolute top-3 right-3 z-20 bg-black/60 text-white text-xs font-semibold px-2 py-1 rounded">
                                <span x-text="active + 1"></span> / {{ $totalImagenes }}
                            </span>

                            {{-- Flecha izquierda --}}
                            <div class="absolute inset-y-0 left-3 z-20 flex items-center">
                                <button type="button" @click="prev()"
                                        class="w-10 h-10 flex items-center justify-center
                                               rounded-full bg-white/80 hover:bg-white text-gray-900 shadow-md
                                               hover:scale-110 transition cursor-pointer"
                                        aria-label="Imagen anterior">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Flecha derecha --}}
                            <div class="absolute inset-y-0 right-3 z-20 flex items-center">
                                <button type="button" @click="next()"
                                        class="w-10 h-10 flex items-center justify-center
                                               rounded-full bg-white/80 hover:bg-white text-gray-900 shadow-md
                                               hover:scale-110 transition cursor-pointer"
                                        aria-label="Imagen siguiente">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Indicadores (puntitos) --}}
                            <div class="absolute bottom-3 left-0 right-0 z-20 flex justify-center gap-1.5">
                                @foreach($coche->imagenes as $idx => $img)
                                    <button type="button"
                                            @click="active = {{ $idx }}"
                                            :class="active === {{ $idx }} ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/75 w-2'"
                                            class="h-2 rounded-full transition-all duration-200"
                                            aria-label="Ir a imagen {{ $idx + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Miniaturas --}}
                    @if($totalImagenes > 1)
                        <div class="p-3 grid grid-cols-5 sm:grid-cols-6 gap-2">
                            @foreach($coche->imagenes as $idx => $img)
                                <button type="button"
                                        @click="active = {{ $idx }}"
                                        :class="active === {{ $idx }} ? 'ring-2 ring-indigo-500 ring-offset-1' : 'opacity-60 hover:opacity-100'"
                                        class="aspect-[4/3] rounded overflow-hidden transition">
                                    <img src="{{ $img->url }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $coche->title }}</h1>
                    <div class="mt-2 flex items-center justify-between flex-wrap gap-3">
                        <x-price-format :value="$coche->price" class="text-3xl font-extrabold text-indigo-600" />
                        <span class="text-sm text-gray-500">
                            {{ $coche->views_count }} visitas
                        </span>
                    </div>

                    <dl class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Marca</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->brand }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Modelo</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->model }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Año</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->year }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Kilómetros</dt>
                            <dd class="font-semibold text-gray-900">{{ number_format($coche->mileage_km, 0, ',', '.') }} km</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Combustible</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->fuel_type->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Cambio</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->transmission->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Carrocería</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->body_type->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Color</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->color ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Provincia</dt>
                            <dd class="font-semibold text-gray-900">{{ $coche->province }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-2">Descripción</h2>
                        <p class="text-gray-700 whitespace-pre-line">{{ $coche->description }}</p>
                    </div>
                </div>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Vendedor</h3>
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            {{ strtoupper(substr($coche->user->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $coche->user->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $coche->user->province }}</div>
                        </div>
                    </div>

                    @auth
                        @if(auth()->id() !== $coche->user_id)
                            @php
                                $existingChat = \App\Models\Chat::where('coche_id', $coche->id)
                                    ->where('buyer_id', auth()->id())
                                    ->first();
                            @endphp

                            @if($existingChat)
                                <a href="{{ route('chats.show', $existingChat) }}"
                                   class="mt-4 inline-flex w-full items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                    Ver conversación
                                </a>
                            @else
                                <form method="POST" action="{{ route('coches.contact', $coche) }}" class="mt-4 space-y-2"
                                      x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
                                    @csrf
                                    <button type="button" x-show="!open" @click="open = true"
                                            class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                        Contactar al vendedor
                                    </button>
                                    <div x-show="open" x-cloak class="space-y-2">
                                        <textarea name="body" rows="3" required
                                                  class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                  placeholder="Hola, estoy interesado en tu coche..."></textarea>
                                        @error('body') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                        <button class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                            Enviar mensaje
                                        </button>
                                    </div>
                                </form>
                            @endif

                            @php($isFav = $coche->isFavoritedBy(auth()->user()))
                            <form method="POST" action="{{ route('coches.favorite', $coche) }}" class="mt-2">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-2 border text-sm font-medium rounded-md
                                            {{ $isFav ? 'bg-rose-50 border-rose-300 text-rose-700 hover:bg-rose-100' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50' }}">
                                    {{ $isFav ? '♥ En favoritos' : '♡ Añadir a favoritos' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('coches.edit', $coche) }}"
                               class="mt-4 inline-block w-full text-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                                Editar mi anuncio
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                           class="mt-4 inline-block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Acceder para contactar
                        </a>
                    @endauth
                </div>
            </aside>
        </div>

        @if($similares->count())
            <section class="mt-12">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Otros {{ $coche->brand }} similares</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($similares as $other)
                        <x-coche-card :coche="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
