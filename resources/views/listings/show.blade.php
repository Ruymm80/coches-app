<x-app-layout>
    <x-slot name="title">{{ $listing->title }} — Coches.app</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Inicio</a>
            <span class="mx-1">/</span>
            <a href="{{ route('listings.index') }}" class="hover:text-indigo-600">Coches</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $listing->title }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div x-data="{ active: 0, images: {{ $listing->images->map(fn ($i) => $i->url)->toJson() }} }"
                     class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="relative aspect-[4/3] bg-gray-100">
                        @if($listing->images->count())
                            <template x-for="(img, idx) in images" :key="idx">
                                <img :src="img" x-show="active === idx"
                                     class="absolute inset-0 w-full h-full object-cover" />
                            </template>
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                Sin imágenes
                            </div>
                        @endif

                        @if($listing->featured)
                            <span class="absolute top-3 left-3 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded">
                                Destacado
                            </span>
                        @endif
                    </div>

                    @if($listing->images->count() > 1)
                        <div class="p-3 grid grid-cols-5 sm:grid-cols-6 gap-2">
                            @foreach($listing->images as $idx => $img)
                                <button type="button"
                                        @click="active = {{ $idx }}"
                                        :class="active === {{ $idx }} ? 'ring-2 ring-indigo-500' : 'opacity-70 hover:opacity-100'"
                                        class="aspect-[4/3] rounded overflow-hidden">
                                    <img src="{{ $img->url }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $listing->title }}</h1>
                    <div class="mt-2 flex items-center justify-between flex-wrap gap-3">
                        <x-price-format :value="$listing->price" class="text-3xl font-extrabold text-indigo-600" />
                        <span class="text-sm text-gray-500">
                            {{ $listing->views_count }} visitas
                        </span>
                    </div>

                    <dl class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Marca</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->brand }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Modelo</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->model }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Año</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->year }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Kilómetros</dt>
                            <dd class="font-semibold text-gray-900">{{ number_format($listing->mileage_km, 0, ',', '.') }} km</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Combustible</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->fuel_type->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Cambio</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->transmission->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Carrocería</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->body_type->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Color</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->color ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Provincia</dt>
                            <dd class="font-semibold text-gray-900">{{ $listing->province }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h2 class="font-semibold text-gray-900 mb-2">Descripción</h2>
                        <p class="text-gray-700 whitespace-pre-line">{{ $listing->description }}</p>
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Vendedor</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            {{ strtoupper(substr($listing->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $listing->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $listing->user->province }}</div>
                        </div>
                    </div>

                    @auth
                        @if(auth()->id() !== $listing->user_id)
                            @php
                                $existingConv = \App\Models\Conversation::where('listing_id', $listing->id)
                                    ->where('buyer_id', auth()->id())
                                    ->first();
                            @endphp

                            @if($existingConv)
                                <a href="{{ route('account.messages.show', $existingConv) }}"
                                   class="mt-4 inline-flex w-full items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                    Ver conversación
                                </a>
                            @else
                                <form method="POST" action="{{ route('listings.contact', $listing) }}" class="mt-4 space-y-2"
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

                            @php($isFav = $listing->isFavoritedBy(auth()->user()))
                            <form method="POST" action="{{ route('listings.favorite', $listing) }}" class="mt-2">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-2 border text-sm font-medium rounded-md
                                            {{ $isFav ? 'bg-rose-50 border-rose-300 text-rose-700 hover:bg-rose-100' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50' }}">
                                    {{ $isFav ? '♥ En favoritos' : '♡ Añadir a favoritos' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('account.listings.edit', $listing) }}"
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

        @if($similar->count())
            <section class="mt-12">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Otros {{ $listing->brand }} similares</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($similar as $other)
                        <x-listing-card :listing="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
